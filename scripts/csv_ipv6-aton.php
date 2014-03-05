<?php
# import a CSV of locations and convert the addresses to inet6-aton

# inet6-aton Documentation
# http://dev.mysql.com/doc/refman/5.6/en/miscellaneous-functions.html#function_inet6-aton

# PHP implementation of inet6-aton with help from
# http://www.highonphp.com/5-tips-for-working-with-ipv6-in-php

if (($handle = fopen("locations.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
      echo $data[0].",".strtoupper(bin2hex(inet_pton(trim($data[1])))).",".strtoupper(bin2hex(inet_pton(trim($data[2])))).",\n";
    }
    fclose($handle);
}
