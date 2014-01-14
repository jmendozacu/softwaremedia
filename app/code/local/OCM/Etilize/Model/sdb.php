<?php
    require('Spex.php');

    $spexCatalogId = 223438;
    $mfgId = 10297;
    $partNumber = '65064073';
    
    //Connect to Etilize and get product listing
    try {
        $catalog = new SpexCatalog($spexCatalogId);
        echo "Success creating SpexCatalog Object\n";
        }
    catch (Exception $e){
        echo "Error creating SpexCatalog Object\n";
        echo $e;
        }
    
    $etilizeResult = $catalog->getProductForId((int)$mfgId, $partNumber);
    
    echo $etilizeResult;
    
    
?>
        