<?php
    class Pixl
    {
        public function colorDistance($c1, $c2)
        {
            $red1 = $c1["red"];
            $red2 = $c2["red"];
            $rmean = ($red1 + $red2) >> 1;
            $r = $red1 - $red2;
            $g = $c1["green"] - $c2["green"];
            $b = $c1["blue"] - $c2["blue"];
            return (sqrt((((512+$rmean)*$r*$r)>>8) + 4*$g*$g + (((767-$rmean)*$b*$b)>>8)));
        }


        public function closestColor($totRed, $totGreen, $totBlue, $colorCount, $colorTab)
        {
            $i = 0;
            $j = 0;
            $colorId = 0;
            $closestScore = 765;
            $res = array();






            //Experimentations algo
            $c1 = array();
            $c1["red"] = $totRed;
            $c1["green"] = $totGreen;
            $c1["blue"] = $totBlue;
            $c2 = array();
            $tmpDistanceTest = 0;
            $distanceTest = 99999999;
            //





            
            while ($i < 51)
            {
                $tmpScore = 0;
                $tmpScore += abs($totRed - $colorTab[$i][0]);
                $tmpScore += abs($totGreen - $colorTab[$i][1]);
                $tmpScore += abs($totBlue - $colorTab[$i][2]);


                
                
                $c2["red"] = $colorTab[$i][0];
                $c2["green"] = $colorTab[$i][1];
                $c2["blue"] = $colorTab[$i][2];
                $tmpDistanceTest = Pixl::colorDistance($c1, $c2);
                if ($tmpDistanceTest < $distanceTest)
                {
                    $distanceTest = $tmpDistanceTest;
                    $closestTest["red"] = $colorTab[$i][0];
                    $closestTest["green"] = $colorTab[$i][1];
                    $closestTest["blue"] = $colorTab[$i][2];
                }




                if ($tmpScore < $closestScore)
                {
                    $closestScore = $tmpScore;
                    $closestColor["red"] = $colorTab[$i][0];
                    $closestColor["green"] = $colorTab[$i][1];
                    $closestColor["blue"] = $colorTab[$i][2];
                    $colorId = $i;
                }
                $i++;
            }
            $colorCount[$colorTab[$colorId][3]] += 1;
            $res["closestColor"] = $closestColor;
            $res["colorCount"] = $colorCount;



            $res["closestTest"] = $closestTest;
            return ($res);
        }

        public function pixlLoop($pixl, $im2)
        {
            $im3 = imagecrop($im2, ['x' => 0, 'y' => 0, 'width' => $pixl * 58, 'height' => $pixl * 58]);
            // im4 = image de test avec l'algo de test
            $im4 = imagecrop($im2, ['x' => 0, 'y' => 0, 'width' => $pixl * 58, 'height' => $pixl * 58]);

            $pixlLoop = $pixl * $pixl;
            $yi = 0;
            $totLine = 0;
            $totLoop = 0;
            $colorTab = Pixl::createColorTab();
            $colorCount = Pixl::colorCountInit($colorTab);
            while ($totLoop < 58)
            {
                $xi = 0;
                $totLine = 0;
                while ($totLine < 58)
                {
                    $totRed  = 0;
                    $totBlue = 0;
                    $totGreen = 0;
                    $i = 0;
                    while ($i < $pixl)
                    {
                        $j = 0;
                        while ($j < $pixl)
                        {
                        $couleur = imagecolorat($im2, $xi + $j, $yi + $i);
                        $tabColor = imagecolorsforindex($im2, $couleur);
                        $totRed += $tabColor["red"];
                        $totBlue += $tabColor["blue"];
                        $totGreen += $tabColor["green"];
                        $j++;
                        }
                        $i++;
                    }
                    $totRed = (int) ($totRed / $pixlLoop);
                    $totBlue = (int) ($totBlue / $pixlLoop);
                    $totGreen = (int) ($totGreen / $pixlLoop);
                    $res = Pixl::closestColor($totRed, $totGreen, $totBlue, $colorCount, $colorTab);
                    $closestColor = $res["closestColor"];
                    $colorCount = $res["colorCount"];

                    //test
                    $closestTest = $res["closestTest"];

                    $averageColor = imagecolorallocate($im2, $totRed, $totGreen, $totBlue);
                    $closestPearl = imagecolorallocate($im3, $closestColor["red"], $closestColor["green"], $closestColor["blue"]);

                    //test
                    $closestPearlTest = imagecolorallocate($im4, $closestTest["red"], $closestTest["green"], $closestTest["blue"]);

                    imagefilledrectangle($im2, $xi, $yi, $xi + $pixl, $yi + $pixl, $averageColor);
                    imagefilledrectangle($im3, $xi, $yi, $xi + $pixl, $yi + $pixl, $closestPearl);

                    //test
                    imagefilledrectangle($im4, $xi, $yi, $xi + $pixl, $yi + $pixl, $closestPearlTest);

                    $xi += $pixl;
                    $totLine++;
                    }
                    $yi += $pixl;
                    $totLoop++;
            }
            imagejpeg($im2, "testallo.jpg", 100);
            imagejpeg($im3, "testPixl.jpg", 100);

            //test
            imagejpeg($im4, "testAlgo.jpg", 100);

            imagedestroy($im2);
            imagedestroy($im3);
            $k = 0;
            echo '<h4><pre>';
            while ($k < 51)
            {
                echo($colorTab[$k][3]);
                echo(":\t");
                echo($colorCount[$colorTab[$k][3]]);
                echo("<br>");
                $k++;
            }
            echo '</pre></h4>';
            return ($colorCount);
        }

        public function createColorTab()
        {
            $colorTab = array();
            // Le tableau de couleur se base sur le nuancier des 62 couleurs de perles Hama, avec des approximations entrées à la main pour chaque couleur
            // nuancier : https://www.hama.dk/media/102651/nuancier.pdf
            $xmlColors = simplexml_load_file("C:/xampp/htdocs/colors.xml");
            $i = 0;
            foreach ($xmlColors as $val)
            {
                $colorTab[$i][0] = intval($val["red"]);
                $colorTab[$i][1] = intval($val["green"]);
                $colorTab[$i][2] = intval($val["blue"]);
                $colorTab[$i][3] = strval($val["name"]);
                $i++;
            }
            echo "<br>";
            // $colorTab[0][0] = 255;  // Blanc
            // $colorTab[0][1] = 255;  // Blanc
            // $colorTab[0][2] = 255;  // Blanc
            // $colorTab[0][3] = "Blanc 01";

            // $colorTab[1][0] = 255;  // Creme
            // $colorTab[1][1] = 255;  // Creme
            // $colorTab[1][2] = 127;  // Creme
            // $colorTab[1][3] = "Creme 02";

            // $colorTab[2][0] = 255;  // Jaune
            // $colorTab[2][1] = 228;  // Jaune
            // $colorTab[2][2] = 0;    // Jaune
            // $colorTab[2][3] = "Jaune 03";

            // $colorTab[3][0] = 255;  // Orange
            // $colorTab[3][1] = 93;   // Orange
            // $colorTab[3][2] = 0;    // Orange
            // $colorTab[3][3] = "Orange 04";

            // $colorTab[4][0] = 255;  // Rouge
            // $colorTab[4][1] = 0;    // Rouge
            // $colorTab[4][2] = 0;    // Rouge
            // $colorTab[4][3] = "Rouge 05";

            // $colorTab[5][0] = 248;  // Rose
            // $colorTab[5][1] = 147;  // Rose
            // $colorTab[5][2] = 147;  // Rose
            // $colorTab[5][3] = "Rose 06";

            // $colorTab[6][0] = 125;  // Violet
            // $colorTab[6][1] = 44;   // Violet
            // $colorTab[6][2] = 119;  // Violet
            // $colorTab[6][3] = "Violet 07";

            // $colorTab[7][0] = 39;  // Bleu foncé
            // $colorTab[7][1] = 45;  // Bleu foncé
            // $colorTab[7][2] = 163; // Bleu foncé
            // $colorTab[7][3] = "Bleu foncé 08";

            // $colorTab[8][0] = 61;  // Bleu clair
            // $colorTab[8][1] = 70;  // Bleu clair
            // $colorTab[8][2] = 232; // Bleu clair
            // $colorTab[8][3] = "Bleu clair 09";

            // $colorTab[9][0] = 31;       // Vert
            // $colorTab[9][1] = 134;      // Vert
            // $colorTab[9][2] = 34;       // Vert
            // $colorTab[9][3] = "Vert 10";

            // $colorTab[10][0] = 50;      // Vert clair
            // $colorTab[10][1] = 212;     // Vert clair
            // $colorTab[10][2] = 55;      // Vert clair
            // $colorTab[10][3] = "Vert clair 11";

            // $colorTab[11][0] = 75;      // Marron
            // $colorTab[11][1] = 11;      // Marron
            // $colorTab[11][2] = 15;      // Marron
            // $colorTab[11][3] = "Marron 12";

            // $colorTab[12][0] = 149;  // Gris
            // $colorTab[12][1] = 149;    // Gris
            // $colorTab[12][2] = 149;    // Gris
            // $colorTab[12][3] = "Gris 17";

            // $colorTab[13][0] = 0;  // Noir
            // $colorTab[13][1] = 0;    // Noir
            // $colorTab[13][2] = 0;    // Noir
            // $colorTab[13][3] = "Noir 18";

            // $colorTab[14][0] = 140;  // Caramel
            // $colorTab[14][1] = 38;    // Caramel
            // $colorTab[14][2] = 38;    // Caramel
            // $colorTab[14][3] = "Caramel 20";

            // $colorTab[15][0] = 192;  // Marron clair
            // $colorTab[15][1] = 116;    // Marron clair
            // $colorTab[15][2] = 64;    // Marron clair
            // $colorTab[15][3] = "Marr clair 21";

            // $colorTab[16][0] = 171;  // Rouge noel
            // $colorTab[16][1] = 0;    // Rouge noel
            // $colorTab[16][2] = 0;    // Rouge noel
            // $colorTab[16][3] = "Rouge noel 22";

            // $colorTab[17][0] = 255;  // Rose mat
            // $colorTab[17][1] = 193;    // Rose mat
            // $colorTab[17][2] = 193;    // Rose mat
            // $colorTab[17][3] = "Rose mat 26";

            // $colorTab[18][0] = 204;  // Beige
            // $colorTab[18][1] = 196;    // Beige
            // $colorTab[18][2] = 136;    // Beige
            // $colorTab[18][3] = "Beige 27";

            // $colorTab[19][0] = 40;  // Vert foncé
            // $colorTab[19][1] = 62;    // Vert foncé
            // $colorTab[19][2] = 38;    // Vert foncé
            // $colorTab[19][3] = "Vert foncé 28";

            // $colorTab[20][0] = 178;  // Lie de vin
            // $colorTab[20][1] = 30;    // Lie de vin
            // $colorTab[20][2] = 53;    // Lie de vin
            // $colorTab[20][3] = "Lie de vin 29";

            // $colorTab[21][0] = 86;  // Turquoise
            // $colorTab[21][1] = 148;    // Turquoise
            // $colorTab[21][2] = 222;    // Turquoise
            // $colorTab[21][3] = "Turquoise 31";

            // $colorTab[22][0] = 255;  // Orange néon
            // $colorTab[22][1] = 113;    // Orange néon
            // $colorTab[22][2] = 38;    // Orange néon
            // $colorTab[22][3] = "Orange néon 38";
            //var_dump($colorTab);
            return ($colorTab);
        }

        public function colorCountInit($colorTab)
        {
            $tab = array();
            $i = 0;
            while ($i < 51)
            {
                $tab[$colorTab[$i][3]] = 0;
                $i++;
            }
            return ($tab);
        }
    }
?>