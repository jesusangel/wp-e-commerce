<?php
class usps {
    var $usps_id, $usps_password, $internal_name, $name;
    function usps () {
        $this->internal_name = "usps";
        $this->name="USPS";
        $this->is_external=true;
        $this->requires_curl=true;
        $this->needs_zipcode=true;
        return true;
    }

    function getId() {
        return $this->usps_id;
    }

    function setId($id) {
        $usps_id = $id;
        return true;
    }

    function getName() {
        return $this->name;
    }

    function getInternalName() {
        return $this->internal_name;
    }

    function getForm() {
        $checked = '';
        if(get_option("usps_test_server") == '1') {
            $checked = 'checked = "checked"';
        }

        $allServices['FIRST CLASS'] = '';
        $allServices['PRIORITY'] = '';
        $allServices['PRIORITY_MAIL'] = '';
        $allServices['PRIORITY_SMALL'] = '';
        $allServices['PRIORITY_REGULAR'] = '';
        $allServices['PRIORITY_LARGE'] = '';
        $allServices['EXPRESS'] = '';
        $allServices['EXPRESS_REGULAR'] = '';
        $allServices['PARCEL POST'] = '';
        $allServices['MEDIA'] = '';
        $allServices['LIBRARY'] = '';

        $selectedServices = explode(", ", get_option('usps_services'));

        foreach($selectedServices as $key){
            $allServices[$key] = 'checked = "checked"';
        }

        $output="<tr>
					<td>
						".__('USPS ID', 'wpsc').":
					</td>
					<td>
						<input type='text' name='uspsid' value='".get_option("uspsid")."' />
					</td>
				</tr>
				<tr>
					<td>
						".__('USPS Password', 'wpsc').":
					</td>
					<td>
						<input type='text' name='uspspw' value='".get_option("uspspw")."' />
					</td>
				</tr>
				<tr>
					<td>
						".__('Use Test Server:','wpsc')."
					</td>
					<td>
						<input type='checkbox' ".$checked." name='usps_test_server' value='1' />
					</td>
				</tr>
                                <tr>
					<td>
						".__('Select services:','wpsc')."
					</td>
					<td>
						<input type='checkbox' ".$allServices['FIRST CLASS']." name='usps_services[]' value='FIRST CLASS' /> First-Class Mail<br />
                                                <input type='checkbox' ".$allServices['PRIORITY']." name='usps_services[]' value='PRIORITY' /> Priority Mail Flat Rate Envelope<br />
                                                <input type='checkbox' ".$allServices['PRIORITY_MAIL']." name='usps_services[]' value='PRIORITY_MAIL' /> Priority Mail<br />
                                                <input type='checkbox' ".$allServices['PRIORITY_SMALL']." name='usps_services[]' value='PRIORITY_SMALL' /> Priority Mail Small Flat Rate Box<br />
                                                <input type='checkbox' ".$allServices['PRIORITY_REGULAR']." name='usps_services[]' value='PRIORITY_REGULAR' /> Priority Mail Medium Flat Rate Box<br />
                                                <input type='checkbox' ".$allServices['PRIORITY_LARGE']." name='usps_services[]' value='PRIORITY_LARGE' /> Priority Mail Large Flat Rate Box<br />
                                                <input type='checkbox' ".$allServices['EXPRESS']." name='usps_services[]' value='EXPRESS' /> Express Mail Flat Rate Envelope<br />
                                                <input type='checkbox' ".$allServices['EXPRESS_REGULAR']." name='usps_services[]' value='EXPRESS_REGULAR' /> Express Mail<br />
                                                <input type='checkbox' ".$allServices['PARCEL POST']." name='usps_services[]' value='PARCEL POST' /> Parcel Post<br />
                                                <input type='checkbox' ".$allServices['MEDIA']." name='usps_services[]' value='MEDIA' /> Media Mail<br />
                                                <input type='checkbox' ".$allServices['LIBRARY']." name='usps_services[]' value='LIBRARY' /> Library Mail<br />
					</td>
				</tr>
                                <tr>
					<td>
						".__('Add amount to rates:','wpsc')."
					</td>
					<td>
						<input type='text' name='usps_extra_cost' value='".get_option("usps_extra_cost")."' />
					</td>
				</tr>
			
				";
        return $output;
    }

    function submit_form() {
        if (isset($_POST['uspsid']) && $_POST['uspsid'] != '') {
            update_option('uspsid', $_POST['uspsid']);
        }
        if (isset($_POST['uspspw']) && $_POST['uspspw'] != '') {
            update_option('uspspw', $_POST['uspspw']);
        }
        if (isset($_POST['usps_extra_cost']) && $_POST['usps_extra_cost'] != '') {
            update_option('usps_extra_cost', $_POST['usps_extra_cost']);
        }
        if(isset($_POST['usps_test_server']) && $_POST['usps_test_server'] != '') {
            update_option('usps_test_server', $_POST['usps_test_server']);
        }else {
            update_option('usps_test_server', '');
        }
        if(isset($_POST['usps_services']) && $_POST['usps_services'] != ''){
            $services = '';
            foreach($_POST['usps_services'] as $key){
                $services .= $key.', ';
            }
            $services = substr($services, 0, -2);
            update_option('usps_services', $services);
        }
        return true;
    }

    function getQuote() {
        global $wpdb, $wpsc_usps_quote;
        if(isset($wpsc_usps_quote) && (count($wpsc_usps_quote)> 0)) {
            return $wpsc_usps_quote;
        }
        if(isset($_POST['zipcode'])) {

            $zipcode = $_POST['zipcode'];
            $_SESSION['wpsc_zipcode'] = $_POST['zipcode'];
        } else if(isset($_SESSION['wpsc_zipcode'])) {
            $zipcode = $_SESSION['wpsc_zipcode'];
        }else {
            $zipcode = get_option('base_zipcode');
        }
        $dest = $_SESSION['wpsc_delivery_country'];
        $weight = wpsc_cart_weight_total();
        $pound = floor($weight);
        $ounce = ($weight-$pound)*16;
        $machinable = 'true';
        if (($ounce > 13) || ($pound > 1)) {
            $serv = get_option('usps_services');
            $serv = str_replace('FIRST CLASS, ', '', $serv);
            $serv = str_replace('FIRST CLASS', '', $serv);
            define('MODULE_SHIPPING_USPS_TYPES', $serv);
        } else {
            define('MODULE_SHIPPING_USPS_TYPES', get_option('usps_services'));
        }

        if (($dest =='US') && ('US'== get_option('base_country'))) {
            $request  = '<RateV3Request USERID="' . get_option('uspsid') . '" PASSWORD="' . get_option('uspspw') . '">';
            $allowed_types = explode(", ", MODULE_SHIPPING_USPS_TYPES);
            $types = array("FIRST CLASS" => 0,
                    "PRIORITY" => 0,
                    "PRIORITY_MAIL" => 0,
                    "PRIORITY_SMALL" => 0,
                    "PRIORITY_REGULAR" => 0,
                    "PRIORITY_LARGE" => 0,
                    "EXPRESS" => 0,
                    "EXPRESS_REGULAR" => 0,
                    "PARCEL POST" => 0,
                    "MEDIA" => 0,
                    "LIBRARY" => 0
            );
            while (list($key, $value) = each($types)) {
                if ( !in_array($key, $allowed_types) ) continue;

                // FIRST CLASS
                if ($key == 'FIRST CLASS') {
                    if($ounce > 3.5) {
                        $FirstClassMailType = '<FirstClassMailType>FLAT</FirstClassMailType>';
                    }
                    else {
                        $FirstClassMailType = '<FirstClassMailType>LETTER</FirstClassMailType>';
                    }
                } else {
                    $FirstClassMailType = '';
                }

                //PRIORITY
                if ($key == 'PRIORITY') {
                    $container = 'FLAT RATE ENVELOPE';
                }

                if ($key == 'PRIORITY_LARGE') {
                    $key = 'PRIORITY';
                    $container = 'LG FLAT RATE BOX';
                }
                if ($key == 'PRIORITY_SMALL') {
                    $key = 'PRIORITY';
                    $container = 'SM FLAT RATE BOX';
                }
                if ($key == 'PRIORITY_REGULAR') {
                    $key = 'PRIORITY';
                    $container = 'MD FLAT RATE BOX';
                }
                if($key == 'PRIORITY_MAIL') {
                    $key = 'PRIORITY';
                    $container = '';
                    $size = 'REGULAR';
                }

                // EXPRESS
                if ($key == 'EXPRESS') {
                    $container = 'FLAT RATE ENVELOPE';
                }

                if ($key == 'EXPRESS_REGULAR') {
                    $key = 'EXPRESS';
                    $container = '';
                    $size = 'REGULAR';
                }

                if ($key == 'PARCEL POST') {
                    $container = 'REGULAR';
                    $machinable = 'false';
                    $size =  'REGULAR';
                }

                if($key == 'MEDIA') {
                    $size = 'REGULAR';
                }

                if($key == 'LIBRARY') {
                    $size = 'REGULAR';
                }

                $pound = round($pound,2);
                $ounce = round($ounce,2);
                $request .= '<Package ID="1">' .
                        '<Service>' . $key . '</Service>' .
                        $FirstClassMailType .
                        '<ZipOrigination>' . get_option("base_zipcode") . '</ZipOrigination>' .
                        '<ZipDestination>' . $zipcode . '</ZipDestination>' .
                        '<Pounds>' . $pound . '</Pounds>' .
                        '<Ounces>' . $ounce . '</Ounces>' .
                        '<Container>' . $container . '</Container>' .
                        '<Size>' . $size . '</Size>' .
                        '<Machinable>' . $machinable . '</Machinable>' .
                        '</Package>';

                if ($transit) {
                    $transitreq  = 'USERID="' . MODULE_SHIPPING_USPS_USERID .
                            '" PASSWORD="' . MODULE_SHIPPING_USPS_PASSWORD . '">' .
                            '<OriginZip>' . STORE_ORIGIN_ZIP . '</OriginZip>' .
                            '<DestinationZip>' . $dest_zip . '</DestinationZip>';

                    switch ($key) {
                        case 'EXPRESS':  $transreq[$key] = 'API=ExpressMail&XML=' .
                                    urlencode( '<ExpressMailRequest ' . $transitreq . '</ExpressMailRequest>');
                            break;
                        case 'PRIORITY': $transreq[$key] = 'API=PriorityMail&XML=' .
                                    urlencode( '<PriorityMailRequest ' . $transitreq . '</PriorityMailRequest>');
                            break;
                        case 'PARCEL':   $transreq[$key] = 'API=StandardB&XML=' .
                                    urlencode( '<StandardBRequest ' . $transitreq . '</StandardBRequest>');
                            break;
                        default: $transreq[$key] = '';
                            break;
                    }
                }
                $services_count++;
            }
            $request .= '</RateV3Request>'; //'</RateRequest>'; //Changed by Greg Deeth April 30, 2008
            $request = 'API=RateV3&XML=' . urlencode($request);
        } else {
            $dest=$wpdb->get_var("SELECT country FROM ".WPSC_TABLE_CURRENCY_LIST." WHERE isocode='".$dest."'");
            if($dest == 'U.K.') {
                $dest = 'Great Britain and Northern Ireland';
            }

            $pound = round($pound,2);
            $ounce = round($ounce,2);
            $request  = '<IntlRateRequest USERID="' . get_option('uspsid') . '" PASSWORD="' . get_option('uspspw') . '">' .
                    '<Package ID="0">' .
                    '<Pounds>' . $pound . '</Pounds>' .
                    '<Ounces>' . $ounce . '</Ounces>' .
                    '<MailType>Package</MailType>' .
                    '<Country>' . $dest . '</Country>' .
                    '</Package>' .
                    '</IntlRateRequest>';
            $request = 'API=IntlRate&XML=' . urlencode($request);
        }
        $usps_server = 'production.shippingapis.com';
        $api_dll = 'shippingAPI.dll';
        if(get_option('usps_test_server') == '1') {
            $url ='http://testing.shippingapis.com/ShippingAPITest.dll?'.$request;
        }else {
            $url = 'http://'.$usps_server.'/' . $api_dll . '?' . $request;
        }
        $ch=curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_NOPROGRESS, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        @ curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_USERAGENT, 'wp-e-commerce');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $body = curl_exec($ch);
        curl_close($ch);
        $rates=array();
        $response=array();
        while (true) {
            if ($start = strpos($body, '<Package ID=')) {
                $body = substr($body, $start);
                $end = strpos($body, '</Package>');
                $response[] = substr($body, 0, $end+10);
                $body = substr($body, $end+9);
            } else {
                break;
            }
        }
        $rates = array();
        if ($dest == get_option('base_country')) {
            if (sizeof($response) == '1') {
                if (ereg('<Error>', $response[0])) {
                    $number = ereg('<Number>(.*)</Number>', $response[0], $regs);
                    $number = $regs[1];
                    $description = ereg('<Description>(.*)</Description>', $response[0], $regs);
                    $description = $regs[1];
                }
            }

            $n = sizeof($response);
            for ($i=0; $i<$n; $i++) {
                if (strpos($response[$i], '<Rate>')) {
                    $service = ereg('<MailService>(.*)</MailService>', $response[$i], $regs);
                    $service = $regs[1];
                    $service = str_replace('&amp;lt;sup&amp;gt;&amp;amp;reg;&amp;lt;/sup&amp;gt;', '<sup>&reg;</sup>', $service);                    					$service = str_replace('&amp;lt;sup&amp;gt;&amp;amp;trade;&amp;lt;/sup&amp;gt;', '<sup>&trade;</sup>', $service);
                    $postage = ereg('<Rate>(.*)</Rate>', $response[$i], $regs);
                    $postage = $regs[1];
                    $postage = $postage + get_option('usps_extra_cost');
                    if($postage <= 0) {
                        continue;
                    }
                    $rates += array($service => $postage);
                    if ($transit) {
                        switch ($service) {
                            case 'EXPRESS':     $time = ereg('<MonFriCommitment>(.*)</MonFriCommitment>', $transresp[$service], $tregs);
                                $time = $tregs[1];
                                if ($time == '' || $time == 'No Data') {
                                    $time = 'Estimated 1 - 2 ' . 'Days';
                                } else {
                                    $time = 'Tomorrow by ' . $time;
                                }
                                break;
                            case 'PRIORITY':    $time = ereg('<Days>(.*)</Days>', $transresp[$service], $tregs);
                                $time = $tregs[1];
                                if ($time == '' || $time == 'No Data') {
                                    $time = 'Estimated 1 - 3 ' . 'Days';
                                } elseif ($time == '1') {
                                    $time .= ' ' . 'Day';
                                } else {
                                    $time .= ' ' . 'Days';
                                }
                                break;
                            case 'PARCEL':      $time = ereg('<Days>(.*)</Days>', $transresp[$service], $tregs);
                                $time = $tregs[1];
                                if ($time == '' || $time == 'No Data') {
                                    $time = 'Estimated 2 - 9 ' . 'Days';
                                } elseif ($time == '1') {
                                    $time .= ' ' . 'Day';
                                } else {
                                    $time .= ' ' . 'Days';
                                }
                                break;
                            case 'First-Class Mail':
                                $time = 'Estimated 1 - 5 ' . 'Days';
                                break;
                            case 'MEDIA':
                                $time = 'Estimated 2 - 9 ' . 'Days';
                                break;
                            case 'BPM':
                                $time = 'Estimated 2 - 9 ' . 'Days';
                                break;
                            default:
                                $time = '';
                                break;
                        }
                        if ($time != '') $transittime[$service] = ': ' . $time . '';
                    }
                }
            }
            $wpsc_usps_quote = $rates;
        } else {
            if (ereg('<Error>', $response[0])) {
                $number = ereg('<Number>(.*)</Number>', $response[0], $regs);
                $number = $regs[1];
                $description = ereg('<Description>(.*)</Description>', $response[0], $regs);
                $description = $regs[1];
            } else {
                $body = $response[0];
                $services = array();
                while (true) {
                    if ($start = strpos($body, '<Service ID=')) {
                        $body = substr($body, $start);
                        $end = strpos($body, '</Service>');
                        $services[] = substr($body, 0, $end+10);
                        $body = substr($body, $end+9);
                    } else {
                        break;
                    }
                }

                //$allowed_types = Array( 'EXPRESS MAIL INT' => "Express Mail International (EMS)", 'EXPRESS MAIL INT FLAT RATE ENV' => "Express Mail International (EMS) Flat-Rate Envelope", 'PRIORITY MAIL INT' => "Priority Mail International", 'PRIORITY MAIL INT FLAT RATE ENV' => "Priority Mail International Flat-Rate Envelope", 'PRIORITY MAIL INT FLAT RATE BOX' => "Priority Mail International Flat-Rate Box", 'FIRST-CLASS MAIL INT' => "First Class Mail International Letters" );
   
$allowed_types = array(

        'Global Express Guaranteed' => 'Global Express Guaranteed&amp;lt;sup&amp;gt;&amp;amp;reg;&amp;lt;/sup&amp;gt; (GXG)**', // Global Express Guaranteed (GXG)**", // SERVICE ID 4
        'Global Express Non-Doc Rect' => 'Global Express Guaranteed&amp;lt;sup&amp;gt;&amp;amp;reg;&amp;lt;/sup&amp;gt; Non-Document Rectangular',  // SERVICE ID 6
        'Global Express Non-Doc Non-Rect' => 'Global Express Guaranteed&amp;lt;sup&amp;gt;&amp;amp;reg;&amp;lt;/sup&amp;gt; Non-Document Non-Rectangular',  // SERVICE ID 7
        'Global Express Envelopes' => 'USPS GXG&amp;lt;sup&amp;gt;&amp;amp;trade;&amp;lt;/sup&amp;gt; Envelopes**', // USPS GXG Envelopes**", // SERVICE ID 12
        'Express Mail Int' => 'Express Mail&amp;lt;sup&amp;gt;&amp;amp;reg;&amp;lt;/sup&amp;gt; International', // Express Mail International", // SERVICE ID 1
        'Express Mail Int Flat Rate Env' => 'Express Mail&amp;lt;sup&amp;gt;&amp;amp;reg;&amp;lt;/sup&amp;gt; International Flat Rate Envelope', // Express Mail International Flat Rate Envelope", // SERVICE ID 10
        'Express Mail Int Legal' => 'Express Mail&amp;lt;sup&amp;gt;&amp;amp;reg;&amp;lt;/sup&amp;gt; International Legal Flat Rate Envelope', // Express Mail International Legal  Flat Rate Envelope", // SERVICE ID 17
        'Priority Mail International' => 'Priority Mail&amp;lt;sup&amp;gt;&amp;amp;reg;&amp;lt;/sup&amp;gt; International', // Priority Mail International", // SERVICE ID 2
        'Priority Mail Int Flat Rate Lrg Box' => 'Priority Mail&amp;lt;sup&amp;gt;&amp;amp;reg;&amp;lt;/sup&amp;gt; International Large Flat Rate Box',  // SERVICE ID 11
        'Priority Mail Int Flat Rate Med Box' => 'Priority Mail&amp;lt;sup&amp;gt;&amp;amp;reg;&amp;lt;/sup&amp;gt; International Medium Flat Rate Box',  // SERVICE ID 9
        'Priority Mail Int Flat Rate Small Box' => 'Priority Mail&amp;lt;sup&amp;gt;&amp;amp;reg;&amp;lt;/sup&amp;gt; International Small Flat Rate Box**',  // SERVICE ID 16
        'Priority Mail Int DVD' => 'Priority Mail&amp;lt;sup&amp;gt;&amp;amp;reg;&amp;lt;/sup&amp;gt; International DVD Flat Rate Box**',  // SERVICE ID 24
        'Priority Mail Int Lrg Video' => 'Priority Mail&amp;lt;sup&amp;gt;&amp;amp;reg;&amp;lt;/sup&amp;gt; International Large Video Flat Rate Box**', // SERVICE ID 25
        'Priority Mail Int Flat Rate Env' => 'Priority Mail&amp;lt;sup&amp;gt;&amp;amp;reg;&amp;lt;/sup&amp;gt; International Flat Rate Envelope**', // SERVICE ID 8
        'Priority Mail Int Legal Flat Rate Env' => 'Priority Mail&amp;lt;sup&amp;gt;&amp;amp;reg;&amp;lt;/sup&amp;gt; International Legal Flat Rate Envelope**', //  SERVICE ID 22
        'Priority Mail Int Padded Flat Rate Env' => 'Priority Mail&amp;lt;sup&amp;gt;&amp;amp;reg;&amp;lt;/sup&amp;gt; International Padded Flat Rate Envelope**', //  SERVICE ID 23
        'Priority Mail Int Gift Card Flat Rate Env' => 'Priority Mail&amp;lt;sup&amp;gt;&amp;amp;reg;&amp;lt;/sup&amp;gt; International Gift Card Flat Rate Envelope**', //  SERVICE ID 18
        'Priority Mail International Small Flat Rate Envelope' =>'Priority Mail International&amp;lt;sup&amp;gt;&amp;amp;reg;&amp;lt;/sup&amp;gt; Small Flat Rate Envelope**', // SERVICE ID 20
	 'Priority Mail International Window Flat Rate Envelope' =>'Priority Mail International&amp;lt;sup&amp;gt;&amp;amp;reg;&amp;lt;/sup&amp;gt; Window Flat Rate Envelope**', // SERVICE ID 19
	 'First-Class Mail International Package' =>'First-Class Mail International&amp;lt;sup&amp;gt;&amp;amp;reg;&amp;lt;/sup&amp;gt; Package**', // SERVICE ID 15
	 'First-Class Mail International Large Envelope' =>'First-Class Mail International&amp;lt;sup&amp;gt;&amp;amp;reg;&amp;lt;/sup&amp;gt; Large Envelope**' // SERVICE ID 14




        );
                $size = sizeof($services);
                for ($i=0, $n=$size; $i<$n; $i++) {
                    if (strpos($services[$i], '<Postage>')) {
                        $service = ereg('<SvcDescription>(.*)</SvcDescription>', $services[$i], $regs);
                        $service = $regs[1];
                        $postage = ereg('<Postage>(.*)</Postage>', $services[$i], $regs);
                        $postage = $regs[1];
                        $time = ereg('<SvcCommitments>(.*)</SvcCommitments>', $services[$i], $tregs);
                        $time = $tregs[1];
                        $time = preg_replace('/Weeks$/', 'Weeks',$time);
                        $time = preg_replace('/Days$/', 'Days', $time);
                        $time = preg_replace('/Day$/', 'Day', $time);
                        if( !in_array($service, $allowed_types) || ($postage < 0) ) continue;
                        $postage = $postage + get_option('usps_extra_cost');
                        $service = str_replace('&amp;lt;sup&amp;gt;&amp;amp;reg;&amp;lt;/sup&amp;gt;', '<sup>&reg;</sup>', $service);                    					
                        $service = str_replace('&amp;lt;sup&amp;gt;&amp;amp;trade;&amp;lt;/sup&amp;gt;', '<sup>&trade;</sup>', $service);
                        $rates += array($service => $postage);
                        if ($time != '') $transittime[$service] = ' (' . $time . ')';
                    }
                }
            }
        }
        $uspsQuote=$rates;
        $wpsc_usps_quote = $rates;
        return $uspsQuote;
    }

    function get_item_shipping() {
    }
}
$usps = new usps();
$wpsc_shipping_modules[$usps->getInternalName()] = $usps;
?>