<?php

namespace App\Http\Controllers\Odoocpe\Grenter;


class CantidadenLetrasController 
{
    var $Void = "";
    var $SP = " ";
    var $Dot = ".";
    var $Zero = "0";
    var $Neg = "Menos";

    function ValorEnLetras($x, $Moneda)
    {
        $s = "";
        $Ent = "";
        $Frc = "";
        $Signo = "";

        if (floatVal($x) < 0)
            $Signo = $this->Neg . " ";
        else
            $Signo = "";

        if (intval(number_format($x, 2, '.', '')) != $x) //<- averiguar si tiene decimales 
            $s = number_format($x, 2, '.', '');
        else
            $s = number_format($x, 2, '.', '');

        $Pto = strpos($s, $this->Dot);

        if ($Pto === false) {
            $Ent = $s;
            $Frc = $this->Void;
        } else {
            $Ent = substr($s, 0, $Pto);
            $Frc =  substr($s, $Pto + 1);
        }

        if ($Ent == $this->Zero || $Ent == $this->Void)
            $s = "CERO ";
        elseif (strlen($Ent) > 7) {
            $s = $this->SubValLetra(intval(substr($Ent, 0,  strlen($Ent) - 6))) .
                "MILLONES " . $this->SubValLetra(intval(substr($Ent, -6, 6)));
        } else {
            $s = $this->SubValLetra(intval($Ent));
        }

        if (substr($s, -9, 9) == "MILLONES " || substr($s, -7, 7) == "MILLÓN ")
            $s = $s . "DE ";

        $s = $s;

        if ($Frc != $this->Void) {
            $s = $s . " CON " . $Frc . "/100";
            //$s = $s . " " . $Frc . "/100"; 
        }
        $letrass = $Signo . $s . " " . $Moneda;
        return ($Signo . $s . " " . $Moneda);
    }


    function SubValLetra($numero)
    {
        $Ptr = "";
        $n = 0;
        $i = 0;
        $x = "";
        $Rtn = "";
        $Tem = "";

        $x = trim("$numero");
        $n = strlen($x);

        $Tem = $this->Void;
        $i = $n;

        while ($i > 0) {
            $Tem = $this->Parte(intval(substr($x, $n - $i, 1) .
                str_repeat($this->Zero, $i - 1)));
            if ($Tem != "CERO")
                $Rtn .= $Tem . $this->SP;
            $i = $i - 1;
        }


        //--------------------- GoSub FiltroMil ------------------------------ 
        $Rtn = str_replace(" MIL MIL", " UN MIL", $Rtn);
        while (1) {
            $Ptr = strpos($Rtn, "MIL ");
            if (!($Ptr === false)) {
                if (!(strpos($Rtn, "MIL ", $Ptr + 1) === false))
                    $this->ReplaceStringFrom($Rtn, "MIL ", "", $Ptr);
                else
                    break;
            } else break;
        }

        //--------------------- GoSub FiltroCiento ------------------------------ 
        $Ptr = -1;
        do {
            $Ptr = strpos($Rtn, "CIEN ", $Ptr + 1);
            if (!($Ptr === false)) {
                $Tem = substr($Rtn, $Ptr + 5, 1);
                if ($Tem == "M" || $Tem == $this->Void);
                else
                    $this->ReplaceStringFrom($Rtn, "CIEN", "CIENTO", $Ptr);
            }
        } while (!($Ptr === false));

        //--------------------- FiltroEspeciales ------------------------------ 
        $Rtn = str_replace("DIEZ UNO", "ONCE", $Rtn);
        $Rtn = str_replace("DIEZ UNO", "ONCE", $Rtn);
        $Rtn = str_replace("DIEZ DOS", "DOCE", $Rtn);
        $Rtn = str_replace("DIEZ TRES", "TRECE", $Rtn);
        $Rtn = str_replace("DIEZ CUATRO", "CATORCE", $Rtn);
        $Rtn = str_replace("DIEZ CINCO", "QUINCE", $Rtn);
        $Rtn = str_replace("DIEZ SEIS", "DIECISEIS", $Rtn);
        $Rtn = str_replace("DIEZ SIETE", "DIECISIETE", $Rtn);
        $Rtn = str_replace("DIEZ OCHO", "DIECIOCHO", $Rtn);
        $Rtn = str_replace("DIEZ NUEVE", "DIECINUEVE", $Rtn);
        $Rtn = str_replace("VEINTE UN", "VEINTIUN", $Rtn);
        $Rtn = str_replace("VEINTE DOS", "VEINTIDOS", $Rtn);
        $Rtn = str_replace("VEINTE TRES", "VEINTITRES", $Rtn);
        $Rtn = str_replace("VEINTE CUATRO", "VEINTICUATRO", $Rtn);
        $Rtn = str_replace("VEINTE CINCO", "VEINTICINCO", $Rtn);
        $Rtn = str_replace("VEINTE SEIS", "VEINTISEIS", $Rtn);
        $Rtn = str_replace("VEINTE SIETE", "VEINTISIETE", $Rtn);
        $Rtn = str_replace("VEINTE OCHO", "VEINTIOCHO", $Rtn);
        $Rtn = str_replace("VEINTE NUEVE", "VEINTINUEVE", $Rtn);

        //--------------------- FiltroUn ------------------------------ 
        if (substr($Rtn, 0, 1) == "M") $Rtn = " " . $Rtn;
        //--------------------- Adicionar Y ------------------------------ 
        for ($i = 65; $i <= 88; $i++) {
            if ($i != 77)
                $Rtn = str_replace("A " . Chr($i), "* Y " . Chr($i), $Rtn);
        }
        $Rtn = str_replace("*", "A", $Rtn);
        return ($Rtn);
    }


    function ReplaceStringFrom(&$x, $OldWrd, $NewWrd, $Ptr)
    {
        $x = substr($x, 0, $Ptr)  . $NewWrd . substr($x, strlen($OldWrd) + $Ptr);
    }


    function Parte($x)
    {
        $Rtn = '';
        $t = '';
        $i = 0;
        do {
            switch ($x) {
                case 0:
                    $t = "CERO";
                    break;
                case 1:
                    $t = "UNO";
                    break;
                case 2:
                    $t = "DOS";
                    break;
                case 3:
                    $t = "TRES";
                    break;
                case 4:
                    $t = "CUATRO";
                    break;
                case 5:
                    $t = "CINCO";
                    break;
                case 6:
                    $t = "SEIS";
                    break;
                case 7:
                    $t = "SIETE";
                    break;
                case 8:
                    $t = "OCHO";
                    break;
                case 9:
                    $t = "NUEVE";
                    break;
                case 10:
                    $t = "DIEZ";
                    break;
                case 20:
                    $t = "VEINTE";
                    break;
                case 30:
                    $t = "TREINTA";
                    break;
                case 40:
                    $t = "CUARENTA";
                    break;
                case 50:
                    $t = "CINCUENTA";
                    break;
                case 60:
                    $t = "SESENTA";
                    break;
                case 70:
                    $t = "SETENTA";
                    break;
                case 80:
                    $t = "OCHENTA";
                    break;
                case 90:
                    $t = "NOVENTA";
                    break;
                case 100:
                    $t = "CIEN";
                    break;
                case 200:
                    $t = "DOSCIENTOS";
                    break;
                case 300:
                    $t = "TRESCIENTOS";
                    break;
                case 400:
                    $t = "CUATROCIENTOS";
                    break;
                case 500:
                    $t = "QUINIENTOS";
                    break;
                case 600:
                    $t = "SEISCIENTOS";
                    break;
                case 700:
                    $t = "SETECIENTOS";
                    break;
                case 800:
                    $t = "OCHOCIENTOS";
                    break;
                case 900:
                    $t = "NOVECIENTOS";
                    break;
                case 1000:
                    $t = "MIL";
                    break;
                case 1000000:
                    $t = "MILLÓN";
                    break;
            }

            if ($t == $this->Void) {
                $i = $i + 1;
                $x = $x / 1000;
                if ($x == 0) $i = 0;
            } else
                break;
        } while ($i != 0);

        $Rtn = $t;
        switch ($i) {
            case 0:
                $t = $this->Void;
                break;
            case 1:
                $t = " MIL";
                break;
            case 2:
                $t = " MILLONES";
                break;
            case 3:
                $t = " BILLONES";
                break;
        }
        return ($Rtn . $t);
    }
}
