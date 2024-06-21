<?php

class FinalResult {
    function results($f) {
        //print_r($f);
        $d = fopen($f, "r");
        if ($d === false) {
            throw new Exception("Failed to open the file: $f");
        }

        $h = fgetcsv($d);
        $expectedColumns = 16;
        
        if ($h === false) {
            fclose($d);
            throw new Exception("Failed to read the header from file: $f");
        }

        // If header has fewer columns, it will fill the rest with empty strings
        if (count($h) < $expectedColumns) {
            $h = array_pad($h, $expectedColumns, '');
        }

        $rcs = [];
        while (!feof($d)) {
            $r = fgetcsv($d);
            if ($r === false) {
                continue; // to skip invalid rows
            }

            // If row has fewer columns, it will fill the rest with empty strings
            if (count($r) < $expectedColumns) {
                $r = array_pad($r, $expectedColumns, '');
            }

            $amt = (float) ($r[8] ?? 0);
            $ban = empty($r[6]) ? "Bank account number missing" : (int) $r[6];
            $bac = empty($r[2]) ? "Bank branch code missing" : $r[2];
            $e2e = empty($r[10]) && empty($r[11]) ? "End to end id missing" : $r[10] . $r[11];

            // Trim and sanitize values where necessary
            $currency = trim($h[0]);
            $bank_account_name = str_replace(" ", "_", strtolower(trim($r[7])));

            $rcd = [
                "amount" => [
                    "currency" => $currency,
                    "subunits" => (int) ($amt * 100)
                ],
                "bank_account_name" => $bank_account_name,
                "bank_account_number" => $ban,
                "bank_branch_code" => $bac,
                "bank_code" => $r[0],
                "end_to_end_id" => $e2e,
            ];

            $rcs[] = $rcd;
        }

        fclose($d);

        return [
            "filename" => basename($f),
            "failure_code" => $h[1] ?? "",
            "failure_message" => $h[2] ?? "",
            "records" => array_filter($rcs) //  only valid records are returned
        ];
    }
}



?>
