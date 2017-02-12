<?php

/**
 * The Default Example Controller Class
 *
 * @author Shreyansh Goel
 */
use Shared\Controller as Controller;

class Home extends Controller {


    public function index() {

        $this->setLayout('layouts/empty');
        /*

        $i = 0;
        $a[$i++] = "Andaman and Nicobar";
        $a[$i++] = "Andhra Pradesh";
        $a[$i++] = "Arunachal Pradesh";
        $a[$i++] = "Assam";
        $a[$i++] = "Bihar";
        $a[$i++] = "Chandigarh";
        $a[$i++] = "Chhattisgarh";
        $a[$i++] = "Dadra and Nagar Haveli";
        $a[$i++] = "Daman and Diu";
        $a[$i++] = "Delhi";
        $a[$i++] = "Goa";
        $a[$i++] = "Gujarat";
        $a[$i++] = "Haryana";
        $a[$i++] = "Himachal Pradesh";
        $a[$i++] = "Jammu and Kashmir";
        $a[$i++] = "Jharkhand";
        $a[$i++] = "Karnataka";
        $a[$i++] = "Kerala";
        $a[$i++] = "Lakshadweep";
        $a[$i++] = "Madhya Pradesh";
        $a[$i++] = "Maharashtra";
        $a[$i++] = "Manipur";
        $a[$i++] = "Meghalaya";
        $a[$i++] = "Mizoram";
        $a[$i++] = "Nagaland";
        $a[$i++] = "Odisha";
        $a[$i++] = "Pondicherry";
        $a[$i++] = "Punjab";
        $a[$i++] = "Rajasthan";
        $a[$i++] = "Sikkim";
        $a[$i++] = "Tamil Nadu";
        $a[$i++] = "Telangana";
        $a[$i++] = "Tripura";
        $a[$i++] = "Uttar Pradesh";
        $a[$i++] = "Uttarakhand";
        $a[$i++] = "West Bengal";


        $j = 0;

        while($j < $i){

            $state = new models\State(array(
                'name' => $a[$j],
                ));

            $state->save();
            $j++;
        }
        
        */

    }
    
}
