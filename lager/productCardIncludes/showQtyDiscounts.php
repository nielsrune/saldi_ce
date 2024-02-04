<?php
    print "<tr><td><b>".findtekst(2041,$sprog_id)."</b></td>";
    if ($special_price && $specialType=='percent') {
        print "<tr><td>".findtekst(2043,$sprog_id)."</td></tr>";
    } else {
        print "<td align=\"right\"><SELECT class=\"inputbox\" NAME=m_type style=\"width: 4em\">";
        if ($m_type == 'amount') {
            print "<option value=\"amount\">kr</option>";
            print "<option value=\"percent\">%</option>";
        } else {
            print "<option value=\"percent\">%</option>";
            print "<option value=\"amount\">kr</option>";
        }
        print "</SELECT> pr</td>";
        print "<td> ".findtekst(2042,$sprog_id)."</td></tr>";
        for ($x=0;$x<count($m_antal_array);$x++) {
            if ($m_antal_array[$x]) {
                if ($incl_moms && $m_type!="percent") $m_rabat_array[$x]*=(100+$incl_moms)/100;
                ($rabatgruppe)? $inputtype="readonly=\"readonly\"":$inputtype="type=\"text\"";
                print "<tr><td>".findtekst(2044,$sprog_id)."</td>";
                print "<td><input $inputtype size=\"5\" style=\"text-align:right\" name=\"m_rabat_array[$x]\" value=".dkdecimal($m_rabat_array[$x],3)."></td><td><input $inputtype size=\"5\" style=\"text-align:right\" name=\"m_antal_array[$x]\" value=\"".dkdecimal($m_antal_array[$x],3)."\"></td></tr>";
            }
        }
    #$x++;
        if (!$rabatgruppe) {
            if (!isset($m_rabat_array[$x])) $m_rabat_array[$x]='';
            if (!isset($m_antal_array[$x])) $m_antal_array[$x]='';
            print "<tr><td>".findtekst(2044,$sprog_id)."</td>";
            print "<td><input class='inputbox' type='text' size='5' style='text-align:right' name='m_rabat_array[$x]'";
            print "value='".dkdecimal($m_rabat_array[$x],3)."'></td>";
            print "<td><input class='inputbox' type='text' size='5' style=text-align:right name='m_antal_array[$x]'";
            print "value='".dkdecimal($m_antal_array[$x],3)."'></td></tr>";
        }
    }
?>
