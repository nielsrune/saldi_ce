<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.4.1/dist/flowbite.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.4.1/dist/flowbite.min.js"></script>
    <title>Document</title>
</head>
<body class="bg-gray-800">
    <?php
        @session_start();
        $s_id=session_id();
        $header = "nix";
        $bg = "nix";
        include("../includes/connect.php");
        include("../includes/online.php");
        
        $query = db_select("SELECT order_id FROM rentalperiod ORDER BY id DESC LIMIT 1", __FILE__ . " linje " . __LINE__);
        $row = db_fetch_array($query);
        $order_id = $row['order_id'];
        $query = db_select("SELECT * FROM ordrer WHERE id = '$order_id'", __FILE__ . " linje " . __LINE__);
        while($row = db_fetch_array($query)){
            $row = array_filter($row, function($key) {
                return !is_numeric($key);
            }, ARRAY_FILTER_USE_KEY);
            $order = $row;
        }
        $query = db_select("SELECT * FROM ordrelinjer WHERE ordre_id = $order_id", __FILE__ . " linje " . __LINE__);
        while($row = db_fetch_array($query)){
            $row = array_filter($row, function($key) {
                return !is_numeric($key);
            }, ARRAY_FILTER_USE_KEY);
            $orderLines[] = $row;
        }
        $order["orderLines"] = $orderLines;
        $varenr = array_map(function($orderLine) {
            return $orderLine["varenr"];
        }, $orderLines);

        $query = db_select("SELECT box1, box2, box3, box4, kodenr FROM grupper WHERE art = 'RA'", __FILE__ . " linje " . __LINE__);
        $currentYear = date('Y');
        $currentMonth = date('m');
        $regnaar;
        while($row = db_fetch_array($query)){
            $box1 = $row['box1']; // Starting month
            $box2 = $row['box2']; // Starting year
            $box3 = $row['box3']; // Ending month
            $box4 = $row['box4']; // Ending year
            $kodenr = $row['kodenr'];

            // Check if the current year and month fall within the range
            if (($currentYear > $box2 || ($currentYear == $box2 && $currentMonth >= $box1)) &&
                ($currentYear < $box4 || ($currentYear == $box4 && $currentMonth <= $box3))) {
                // The current year and month fall within the range
                // Do something with $kodenr
                $regnaar = $kodenr;
            }
        }

        $varenrGruppe = [];

        foreach ($varenr as $vare) {
            if ($vare == "" || $vare == null) {
                continue;
            }
            $query = db_select("SELECT gruppe FROM varer WHERE varenr = '$vare'", __FILE__ . " linje " . __LINE__);
            $gruppe = db_fetch_array($query)["gruppe"];
            $grupper[] = $gruppe;
            $varenrGruppe[] = ["gruppe" => $gruppe, "varenr" => $vare];
        }

        foreach ($grupper as $gruppe) {
            if ($gruppe == "" || $gruppe == null) {
                continue;
            }
            $q_vg = db_select("SELECT beskrivelse, kodenr, box4, box7 FROM grupper WHERE fiscal_year = $regnaar AND art = 'VG' AND kodenr = $gruppe", __FILE__ . " linje " . __LINE__);
            $r_vg = db_fetch_array($q_vg);

            if ($r_vg && is_array($r_vg)) {
                # Get momssats, checks if it is not momsfri
                if ($r_vg["box7"] != "on") {
                    $q_konto = db_select("SELECT moms FROM kontoplan WHERE regnskabsaar = $regnaar AND kontonr = {$r_vg['box4']}", __FILE__ . " linje " . __LINE__);
                    $konto_result = db_fetch_array($q_konto);
                    $momstype = $konto_result ? $konto_result["moms"] : null;

                    if ($momstype) {
                        $momstype = trim($momstype, 'S');
                        $q_moms = db_select("SELECT box2 FROM grupper WHERE fiscal_year = $regnaar AND kode = 'S' AND kodenr = $momstype AND art = 'SM'", __FILE__ . " linje " . __LINE__);
                        $moms_result = db_fetch_array($q_moms);
                        $momssats = $moms_result ? $moms_result["box2"] / 100 : 0;
                    } else {
                        $momssats = 0;
                    }
                } else {
                    $momssats = 0;
                }

                foreach ($varenrGruppe as $vg) {
                    if ($vg["gruppe"] == $gruppe) {
                        $orderMoms["vareId"] = $vg["varenr"];
                        $orderMoms["moms"] = $momssats;
                    }
                }
            } else {
                // Handle the case where $r_vg is not an array
                error_log("Failed to fetch data for gruppe: $gruppe");
            }
        }
    ?>
   <div class="max-w-4xl mx-auto bg-gray-600 p-8 my-10 shadow-lg text-white">
    <h1 class="text-3xl font-bold mb-4">Faktura</h1>
        <div class="mb-4">
            <h2 class="text-xl font-semibold orderId">Ordre ID: </h2>
            <p class="date">Date: </p>
        </div>
        <div class="mb-4">
            <h3 class="text-lg font-semibold">Kunde Detaljer</h3>
            <p class="navn">Navn: </p>
            <p class="email">Email: </p>
        </div>
        <div class="mb-4">
            <h3 class="text-lg font-semibold">Ordrer Detaljer</h3>
            <div class="relative overflow-x-auto">
    <table class="w-full text-sm text-left rtl:text-right text-white">
        <thead class="text-xs uppercase bg-gray-700">
            <tr>
                <th scope="col" class="px-6 py-3">
                    Product
                </th>
                <th scope="col" class="px-6 py-3 enhed">
                    Antal
                </th>
                <th scope="col" class="px-6 py-3">
                    Pris
                </th>
                <th scope="col" class="px-6 py-3">
                    Rabat
                </th>
                <th scope="col" class="px-6 py-3">
                    Total
                </th>
            </tr>
        </thead>
        <tbody class="tBody">
            
        </tbody>
    </table>
</div>
        </div>
        <div class="text-right">
            <h3 class="text-lg font-semibold total">Total: </h3>
        </div>
    </div>
    <script>
        const order = <?php echo json_encode($order); ?>;
        const moms = <?php echo json_encode($orderMoms); ?>;
        console.log(moms)
        const tBody = document.querySelector('.tBody')
        const enhed = document.querySelector('.enhed')
        const orderId = document.querySelector('.orderId')
        const date = document.querySelector('.date')
        const total = document.querySelector('.total')
        const navn = document.querySelector('.navn')
        const email = document.querySelector('.email')
        navn.innerHTML = `Navn: ${order.firmanavn}`
        email.innerHTML = `Email: ${order.email}`
        orderId.innerHTML = `Ordre ID: ${order.id}`
        date.innerHTML = `Date: ${order.ordredate}`
        total.innerHTML = `Total: ${parseFloat(order.sum).toFixed(2)} kr.`
        order.orderLines.forEach(orderLine => {
            const tr = document.createElement('tr')
            tr.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="ml-4">
                            <div class="text-sm font-medium">
                                ${orderLine.beskrivelse}
                            </div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm">${parseFloat(orderLine.antal).toFixed(0)} ${(orderLine.enhed != null) ? orderLine.enhed : ""}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm">${parseFloat(orderLine.pris).toFixed(2) + parseFloat()}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm">${parseFloat(orderLine.rabat).toFixed(2)}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm">${orderLine.pris * orderLine.antal - orderLine.rabat * orderLine.antal}</div>
                </td>
            `
            tBody.appendChild(tr)
        })
        
    </script>
</body>
</html>