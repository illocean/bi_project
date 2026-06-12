<?php
require __DIR__ . '/bootstrap.php';

use Models\SourceProduct;
use Models\SourceOffice;
use Models\SourceEmployee;
use Models\SourceCustomer;
use Models\SourceOrder;
use Models\SourceOrderDetail;
use Models\DimProduct;
use Models\DimOffice;
use Models\DimEmployee;
use Models\DimCustomer;
use Models\DimOrder;
use Models\DimDate;
use Illuminate\Database\Capsule\Manager as Capsule;

// -----------------------------
// Simple Full-Refresh ETL
// Extract from bi_products.products -> Load into bi_project.dim_product
// Echoes stages: EXTRACT, TRANSFORM, LOAD
// -----------------------------

echo "<br><b>--- EXTRACT ---</b><br>";

$rows = SourceProduct::all(['productCode', 'productName', 'productLine', 'productScale', 'productVendor'])->toArray();

$total_extracted = count($rows);
echo "Total extracted: $total_extracted<br>";

echo "<br><b>--- TRANSFORM ---</b><br>";

$transformed = [];
foreach ($rows as $r) {
    $code = isset($r['productCode']) ? trim($r['productCode']) : null;
    $name = isset($r['productName']) ? trim($r['productName']) : null;

    if ($code === null || $name === null) {
        echo "Skipping malformed row (missing code or name).<br>";
        continue;
    }

    $transformed[] = [
        'productCode' => $code,
        'productName' => $name,
        'productLine' => trim($r['productLine'] ?? ''),
        'productScale' => trim($r['productScale'] ?? ''),
        'productVendor' => trim($r['productVendor'] ?? ''),
    ];
}

$total_transformed = count($transformed);
echo "Total after transform: $total_transformed<br>";

echo "<br><b>--- LOAD ---</b><br>";

try {
    Capsule::connection('target')->statement('SET FOREIGN_KEY_CHECKS=0');
    DimProduct::truncate();
    Capsule::connection('target')->statement('SET FOREIGN_KEY_CHECKS=1');
    DimProduct::insert($transformed);
    $inserted = count($transformed);
    echo "Inserted: $inserted rows into dim_product<br>";
} catch (\Exception $e) {
    echo "Load failed: " . $e->getMessage() . "<br>";
    exit(1);
}

echo "<br><b>--- RESULT ---</b><br>";
echo "Extracted: $total_extracted rows<br>";
echo "Transformed: $total_transformed rows<br>";
echo "Loaded: $inserted rows<br>";

// -----------------------------
// Extended ETL: load other dims and facts
// -----------------------------

echo "<br><b>--- EXTRACT & LOAD: Other Dimensions ---</b><br>";

try {
    Capsule::connection('target')->statement('SET FOREIGN_KEY_CHECKS=0');

    // 1) dim_office <- offices
    echo "Loading dim_office...<br>";
    DimOffice::truncate();
    $offices = SourceOffice::all(['officeCode', 'city', 'state', 'country', 'territory'])->toArray();
    DimOffice::insert($offices);
    echo "Loaded " . count($offices) . " offices.<br>";

    // 2) dim_employee <- employees
    echo "Loading dim_employee...<br>";
    DimEmployee::truncate();
    $emps = SourceEmployee::all(['employeeNumber', 'lastName', 'firstName', 'jobTitle'])->toArray();
    DimEmployee::insert($emps);
    echo "Loaded " . count($emps) . " employees.<br>";

    // 3) dim_customer <- customers
    echo "Loading dim_customer...<br>";
    DimCustomer::truncate();
    $custs = SourceCustomer::all(['customerNumber', 'customerName', 'city', 'state', 'country'])->toArray();
    DimCustomer::insert($custs);
    echo "Loaded " . count($custs) . " customers.<br>";

    // 4) dim_date <- distinct order dates
    echo "Loading dim_date...<br>";
    DimDate::truncate();
    $dates = SourceOrder::distinct()->whereNotNull('orderDate')->pluck('orderDate')->toArray();
    $dateRows = [];
    foreach ($dates as $full) {
        $dt = new \DateTime($full);
        $year = (int) $dt->format('Y');
        $month = (int) $dt->format('n');
        $month_name = $dt->format('F');
        $q = (int) ceil($month / 3);
        $date_key = (int) $dt->format('Ymd');
        $dateRows[] = [
            'date_key' => $date_key,
            'full_date' => $full,
            'month_number' => $month,
            'month_name' => $month_name,
            'quarter' => $q,
            'year' => $year,
        ];
    }
    DimDate::insert($dateRows);
    echo "Loaded " . count($dates) . " dates.<br>";

    // 5) dim_order <- orderdetails JOIN orders (one row per order line)
    echo "Loading dim_order...<br>";
    DimOrder::truncate();
    $orderLines = Capsule::connection('source')
        ->table('orderdetails')
        ->join('orders', 'orderdetails.orderNumber', '=', 'orders.orderNumber')
        ->select('orderdetails.orderNumber', 'orderdetails.orderLineNumber', 'orders.status')
        ->get()
        ->toArray();
    $orderLineArray = json_decode(json_encode($orderLines), true);
    DimOrder::insert($orderLineArray);
    echo "Loaded " . count($orderLineArray) . " order lines.<br>";

    Capsule::connection('target')->statement('SET FOREIGN_KEY_CHECKS=1');

    // Build lookup maps from target dims using pluck
    $productMap = DimProduct::pluck('product_key', 'productCode')->toArray();
    $customerMap = DimCustomer::pluck('customer_key', 'customerNumber')->toArray();
    $officeMap = DimOffice::pluck('office_key', 'officeCode')->toArray();
    $employeeMap = DimEmployee::pluck('employee_key', 'employeeNumber')->toArray();

    $orderRows = DimOrder::all(['order_key', 'orderNumber', 'orderLineNumber'])->toArray();
    $orderMap = [];
    foreach ($orderRows as $o) {
        $orderMap[$o['orderNumber'] . '|' . $o['orderLineNumber']] = $o['order_key'];
    }

    $primaryOrderKeyMap = [];
    foreach ($orderRows as $o) {
        if (!isset($primaryOrderKeyMap[$o['orderNumber']])) {
            $primaryOrderKeyMap[$o['orderNumber']] = $o['order_key'];
        }
    }

    $dateMap = DimDate::pluck('date_key', 'full_date')->toArray();
    $sourceCustomerSalesRepMap = [];
    foreach (SourceCustomer::all(['customerNumber', 'salesRepEmployeeNumber'])->toArray() as $customer) {
        $sourceCustomerSalesRepMap[$customer['customerNumber']] = $customer['salesRepEmployeeNumber'];
    }

    $sourceEmployeeOfficeMap = [];
    foreach (SourceEmployee::all(['employeeNumber', 'officeCode'])->toArray() as $employee) {
        $sourceEmployeeOfficeMap[$employee['employeeNumber']] = $employee['officeCode'];
    }

    // 6) fact_product_sales
    echo "Loading fact_product_sales...<br>";
    Capsule::connection('target')->table('fact_product_sales')->truncate();
    $details = SourceOrderDetail::all(['orderNumber', 'productCode', 'quantityOrdered', 'priceEach', 'orderLineNumber'])->toArray();
    $factRows = [];
    foreach ($details as $d) {
        $pkey = isset($productMap[$d['productCode']]) ? $productMap[$d['productCode']] : null;
        $okey = isset($orderMap[$d['orderNumber'] . '|' . $d['orderLineNumber']]) ? $orderMap[$d['orderNumber'] . '|' . $d['orderLineNumber']] : null;
        if ($pkey === null || $okey === null) {
            continue;
        }
        $qty = (int) $d['quantityOrdered'];
        $amount = (float) $d['priceEach'] * $qty;
        $factRows[] = [
            'product_key' => $pkey,
            'order_key' => $okey,
            'quantityOrdered' => $qty,
            'salesAmount' => $amount,
        ];
    }
    Capsule::connection('target')->table('fact_product_sales')->insert($factRows);
    echo "Loaded " . count($factRows) . " product-sales rows.<br>";

    // 7) fact_customer_sales (aggregated by order)
    echo "Loading fact_customer_sales (aggregated)...<br>";
    Capsule::connection('target')->table('fact_customer_sales')->truncate();
    $orderTotals = Capsule::connection('source')
        ->table('orderdetails')
        ->selectRaw('orderNumber, SUM(quantityOrdered * priceEach) AS total')
        ->groupBy('orderNumber')
        ->get()
        ->toArray();
    $orderTotals = json_decode(json_encode($orderTotals), true);
    $custFactRows = [];
    foreach ($orderTotals as $ot) {
        $orderNumber = $ot['orderNumber'];
        $total = (float) $ot['total'];

        $order = SourceOrder::where('orderNumber', (int) $orderNumber)->first(['customerNumber']);
        if (!$order) continue;

        $custNum = $order->customerNumber;
        $custKey = isset($customerMap[$custNum]) ? $customerMap[$custNum] : null;

        $rep_order_key = null;
        foreach ($orderMap as $k => $v) {
            if (strpos($k, $orderNumber . '|') === 0) {
                $rep_order_key = $v;
                break;
            }
        }

        if ($custKey === null || $rep_order_key === null) continue;

        $custFactRows[] = [
            'customer_key' => $custKey,
            'order_key' => $rep_order_key,
            'salesAmount' => $total,
        ];
    }
    Capsule::connection('target')->table('fact_customer_sales')->insert($custFactRows);
    echo "Loaded " . count($custFactRows) . " customer-sales rows.<br>";

    // 8) fact_market_sales (same grain as customer sales, used for market/geographic analysis)
    echo "Loading fact_market_sales...<br>";
    Capsule::connection('target')->table('fact_market_sales')->truncate();
    $marketFactRows = [];
    foreach ($orderTotals as $ot) {
        $orderNumber = $ot['orderNumber'];
        $total = (float) $ot['total'];

        $order = SourceOrder::where('orderNumber', (int) $orderNumber)->first(['customerNumber']);
        if (!$order) continue;

        $custNum = $order->customerNumber;
        $customerKey = isset($customerMap[$custNum]) ? $customerMap[$custNum] : null;
        $orderKey = isset($primaryOrderKeyMap[$orderNumber]) ? $primaryOrderKeyMap[$orderNumber] : null;

        if ($customerKey === null || $orderKey === null) continue;

        $marketFactRows[] = [
            'customer_key' => $customerKey,
            'order_key' => $orderKey,
            'salesAmount' => $total,
        ];
    }
    Capsule::connection('target')->table('fact_market_sales')->insert($marketFactRows);
    echo "Loaded " . count($marketFactRows) . " market-sales rows.<br>";

    // 9) fact_support_sales (customer -> employee -> office)
    echo "Loading fact_support_sales...<br>";
    Capsule::connection('target')->table('fact_support_sales')->truncate();
    $supportFactRows = [];
    foreach ($orderTotals as $ot) {
        $orderNumber = $ot['orderNumber'];
        $total = (float) $ot['total'];

        $order = SourceOrder::where('orderNumber', (int) $orderNumber)->first(['customerNumber']);
        if (!$order) continue;

        $custNum = $order->customerNumber;
        $employeeNumber = isset($sourceCustomerSalesRepMap[$custNum]) ? $sourceCustomerSalesRepMap[$custNum] : null;
        $officeCode = ($employeeNumber !== null && isset($sourceEmployeeOfficeMap[$employeeNumber])) ? $sourceEmployeeOfficeMap[$employeeNumber] : null;

        $employeeKey = ($employeeNumber !== null && isset($employeeMap[$employeeNumber])) ? $employeeMap[$employeeNumber] : null;
        $officeKey = ($officeCode !== null && isset($officeMap[$officeCode])) ? $officeMap[$officeCode] : null;
        $orderKey = isset($primaryOrderKeyMap[$orderNumber]) ? $primaryOrderKeyMap[$orderNumber] : null;

        if ($employeeKey === null || $officeKey === null || $orderKey === null) continue;

        $supportFactRows[] = [
            'office_key' => $officeKey,
            'employee_key' => $employeeKey,
            'order_key' => $orderKey,
            'salesAmount' => $total,
        ];
    }
    Capsule::connection('target')->table('fact_support_sales')->insert($supportFactRows);
    echo "Loaded " . count($supportFactRows) . " support-sales rows.<br>";

    // 10) fact_temporal_sales (order date to date dimension)
    echo "Loading fact_temporal_sales...<br>";
    Capsule::connection('target')->table('fact_temporal_sales')->truncate();
    $temporalFactRows = [];
    foreach ($orderTotals as $ot) {
        $orderNumber = $ot['orderNumber'];
        $total = (float) $ot['total'];

        $order = SourceOrder::where('orderNumber', (int) $orderNumber)->first(['orderDate']);
        if (!$order) continue;

        $dateKey = isset($dateMap[$order->orderDate]) ? $dateMap[$order->orderDate] : null;
        $orderKey = isset($primaryOrderKeyMap[$orderNumber]) ? $primaryOrderKeyMap[$orderNumber] : null;

        if ($dateKey === null || $orderKey === null) continue;

        $temporalFactRows[] = [
            'date_key' => $dateKey,
            'order_key' => $orderKey,
            'salesAmount' => $total,
        ];
    }
    Capsule::connection('target')->table('fact_temporal_sales')->insert($temporalFactRows);
    echo "Loaded " . count($temporalFactRows) . " temporal-sales rows.<br>";
} catch (\Exception $e) {
    echo "Extended ETL failed: " . $e->getMessage() . "<br>";
    exit(1);
}

echo "<br><b>--- ETL COMPLETE ---</b><br>";