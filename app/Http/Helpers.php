<?php
use App\Models\User;
use App\Models\UserAuthLogin;
use App\Models\ServiceType;

class Helpers
{

    public static function sendSuccessResponse($result = [], $code = 200, $token = '')
    {

        if(is_array($result) && count($result) == 0){
            $result = (object)$result;
        }
        $response = [
            'success'   => $result
        ];
        if($token && $token != ''){
            return response()->json($response, $code)->header('token', $token);
        }
        return response()->json($response, $code);
    }

    /*
     * function for send failure response
     */
    public static function sendFailureResponse($message = 'Something went wrong.', $code = 422)
    {
        $response = [
            'error'   => $message,
        ];

        return response($response, $code);
    }

    public static function generateApiToken(){
        mt_srand((double)microtime()*10000);
        $uuid = rand(1,99999).time();
        $salt = substr(sha1(uniqid(mt_rand(), true)), 0, 40);
        return substr(sha1($salt) . $salt,1,85).$uuid;
    }


    public static function getSignature() {
        $clientId = config('constant.cashfree_payout.test_client_id');;
        $publicKey =
    openssl_pkey_get_public(file_get_contents("/path/to/certificate/public
    _key.pem"));
        $encodedData = $clientId.".".strtotime("now");
        return static::encrypt_RSA($encodedData, $publicKey);
    }

    private static function encrypt_RSA($plainData, $publicKey) { if (openssl_public_encrypt($plainData, $encrypted, $publicKey,
    OPENSSL_PKCS1_OAEP_PADDING))
          $encryptedData = base64_encode($encrypted);
        else return NULL;
        return $encryptedData;
    }
    
    public static function velidateAuthToken($token){
        $tokenExist  = UserAuthLogin::where('api_token',$token)->with('user')->first();
        if($tokenExist){
            return $tokenExist;
        }
        return false;
    }
    /*
    	Get Main service
    */
    public static function getServiceName($service_id =''){
        if($service_id != ''){
            $service = ServiceType::findorfail($service_id);
            if($service){
                return $service->name;
            }else{
                return '--';
            }
        }else{
            return '--';
        }
    }

    public static function checkNull($val = null)
    {
        if ($val == '' || $val == null) {
            return '-';
        } else {
            return $val;
        }
    }


    public static function checkVerified($value = 0){
        if ($value) {
            return 'Yes';
        }else{
            return 'No';
        }
    }

    /**
     * function for check empty value
     * @param $value
     */
    public static function checkEmpty($value = null)
    {
        if (isset($value) && !empty($value)) {
            $data = trim(strip_tags($value));
            return iconv('ISO-8859-1', 'ASCII//IGNORE', $data);
        } else {
            return null;
        }
    }

    /**
     * function for Common Datetime picker formate
     * @param $value
     */
    public static function commonDateTimePickerFormate($value = null)
    {
        if (isset($value) && !empty($value) && ($value != '0000-00-00' && $value != '0000-00-00 00:00:00' && $value != '1970-01-01')) {
            $value = trim($value);
            return date('m/d/Y H:i A', strtotime($value));
        } else {
            return null;
        }
    }

    /**
     * function for Common Date Formate
     * @param $value
     */
    public static function commonDateFormate($value = null)
    {
        if (isset($value) && !empty($value) && ($value != '0000-00-00' && $value != '0000-00-00 00:00:00' && $value != '1970-01-01')) {
            $value = trim($value);
            return date('d F, Y', strtotime($value));
        } else {
            return 'NA';
        }
    }

    public static function commonTimeFormate($value = null)
    {
        if (isset($value) && !empty($value) && ($value != '0000-00-00' && $value != '0000-00-00 00:00:00' && $value != '1970-01-01')) {
            $value = trim($value);
            return date('H:i A', strtotime($value));
        } else {
            return 'NA';
        }
    }

    /**
     * function for Common Date Formate
     * @param $value
     */
    public static function commonDateTimeFormate($value = null)
    {
        if (isset($value) && !empty($value) && ($value != '0000-00-00' && $value != '0000-00-00 00:00:00' && $value != '1970-01-01')) {
            $value = trim($value);
            return date('d F, Y H:i A', strtotime($value));
        } else {
            return null;
        }
    }

    /**
     * function for check empty date
     * @param $value
     */
    public static function checkEmptydate($value = null)
    {
        if (isset($value) && !empty($value) && ($value != '0000-00-00' && $value != '0000-00-00 00:00:00' && $value != '1970-01-01')) {
            $value = trim($value);
            return date('Y-m-d', strtotime($value));
        } else {
            return 'NA';
        }
    }

    /**
     * function for check empty date
     * @param $value
     */
    public static function checkEmptydateMdY($value = null)
    {
        if (isset($value) && !empty($value) && ($value != '0000-00-00' && $value != '0000-00-00 00:00:00' && $value != '1970-01-01')) {
            $value = trim($value);
            return date('d M Y', strtotime($value));
        } else {
            return null;
        }
    }

    /**
     * function for check empty date
     * @param $value
     */
    public static function checkEmptydateMdYHIS($value = null)
    {
        if (isset($value) && !empty($value) && ($value != '0000-00-00' && $value != '0000-00-00 00:00:00' && $value != '1970-01-01')) {
            $value = trim($value);
            return date('d M Y h:i A', strtotime($value));
        } else {
            return "--";
        }
    }

    /**
     * function for check empty date
     * @param $value
     */
    public static function checkEmptydateTime($value = null)
    {
        if (isset($value) && !empty($value) && ($value != '0000-00-00' && $value != '0000-00-00 00:00:00' && $value != '1970-01-01')) {
            $value = trim($value);
            return date('Y-m-d H:i:s', strtotime($value));
        } else {
            return null;
        }
    }

    /**
     * function for check empty date with slash
     * @param $value
     */
    public static function checkEmptydateWithSlash($value = null)
    {
        if (isset($value) && !empty($value) && ($value != '0000-00-00' && $value != '0000-00-00 00:00:00' && $value != '1970-01-01')) {
            $value = trim($value);
            return date('d/m/Y H:i', strtotime($value));
        } else {
            return null;
        }
    }

  
    /*
     * Method to strip tags globally.
     */

    public static function globalXssClean()
    {
        // Recursive cleaning for array [] inputs, not just strings.
        $sanitized = static::arrayStripTags(Request::all());
        Request::merge($sanitized);
    }

    /**
     * Method to strip tags
     *
     * @param $array
     * @return array
     */
    public static function arrayStripTags($array)
    {
        $result = [];

        foreach ($array as $key => $value) {
            // Don't allow tags on key either, maybe useful for dynamic forms.
            $key = strip_tags($key);

            // If the value is an array, we will just recurse back into the
            // function to keep stripping the tags out of the array,
            // otherwise we will set the stripped value.
            if (is_array($value)) {
                $result[$key] = static::arrayStripTags($value);
            } else {
                // I am using strip_tags(), you may use htmlentities(),
                // also I am doing trim() here, you may remove it, if you wish.
                $result[$key] = trim(strip_tags($value));
            }
        }

        return $result;
    }

    /**
     * Escape output
     *
     * @param $value
     * @return string
     */
    public static function sanitizeOutput($value)
    {
        return addslashes($value);
    }

    /*
     * Convert date
     */

    public static function convertDate($convertDate)
    {
        if ($convertDate != '') {
            $convertDate = str_replace('/', '-', $convertDate);
            return date('Y-m-d', strtotime($convertDate));
        }
    }

    /**
     * Send success ajax response
     *
     * @param string $message
     * @param array $result
     * @return array
     */
    public static function sendSuccessAjaxResponse($message = '', $result = [])
    {
        $response = [
            'status' => true,
            'message' => $message,
            'data' => $result,
        ];

        return $response;
    }

    /**
     * Send failure ajax response
     *
     * @param string $message
     * @return array
     */
    public static function sendFailureAjaxResponse($message = '', $data = [])
    {
        $message = $message == '' ? config('app.message.default_error') : $message;

        $response = [
            'status' => false,
            'message' => $message,
        ];

        return $response;
    }



    /**
     * function for send email
    */
    public static function sendEmail($template, $data, $toEmail, $toName, $subject, $fromName = '', $fromEmail = '',$attachment = '') {
        if ($fromEmail == '') {
            $fromEmail ='gaurav@technofox.com';
        }
        try {
            $fromName = 'Cars';
            $data = \Mail::send($template, $data, function ($message) use($toEmail, $toName, $subject, $data, $fromName, $fromEmail, $attachment) {
                $message->to($toEmail, $toName);
                $message->subject($subject);
                if ($fromEmail != '' && $fromName != '') {
                    $message->from($fromEmail, $fromName);
                }
                if($attachment != ''){
                    $message->attach($attachment);
                }
            });
            return 1;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    /**
     * Generate password
     * @param int $length
     * @return string
     */
    public static function generatePassword($length = 12)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
        $password = substr(str_shuffle($chars), 0, $length);
        return $password;
    }


    /**
     * Generate otp
     * @param int $length
     * @return string
     */
    public static function generateOtp($length = 6)
    {
        $chars = "1234567890";
        $otp = substr(str_shuffle($chars), 0, $length);
        return $otp;
    }

    /**
     * Resize image
     * @param $fileToResize
     * @return mixed
     */
    public static function resizeImage($imageToResize)
    {
        $img = Image::make($imageToResize)
            ->resize(1200, null)
            ->encode('jpg', 80)
            ->save();
        return $img->basename;
    }

    /**
     * Convert image to jpg
     * @param $imageToConvert
     * @param $convertedFile
     * @return string
     */
    public static function convertToJpg($imageToConvert, $convertedFile)
    {
        $img = Image::make($imageToConvert)
            ->encode('jpg', 80)
            ->save($convertedFile);
        unlink($imageToConvert);
        return $img->dirname . '/' . $img->basename;
    }

    /**
     * Get image width
     * @param $image
     * @return mixed
     */
    public static function getImageWidth($image)
    {
        return Image::make($image)->width();
    }

    /**
     * Get image height
     * @param $image
     * @return mixed
     */
    public static function getImageHeight($image)
    {
        return Image::make($image)->height();
    }

    /**
     * Create folder
     * @param $path
     * @return bool
     */
    public static function createFolder($path)
    {
        return \File::makeDirectory($path, 0777);
    }

    /**
     * Upload files other than images
     * @param $document
     * @param $dir
     * @return string
     */
    public static function uploadDocuments($document, $dir, $fileName = '')
    {
        $date = new DateTime();
        $currentTimeStamp = $date->getTimestamp();

        if ($fileName == '') {
            $documentOriginalName = $currentTimeStamp . '_' . $document->getFilename() . '.' . $document->getClientOriginalExtension();
        } else {
            $documentOriginalName = $fileName . '.' . $document->getClientOriginalExtension();

            //Remove file first if exist
            if (file_exists($dir . $documentOriginalName)) {
                unlink($dir . $documentOriginalName);
            }
        }

        //Store file to folder
        $document->move($dir, $documentOriginalName);
        return $documentOriginalName;
    }

    /**
     * function for add http in url
     * @param $url
     * @return string
     */
    public static function addHttpToUrl($url)
    {
        if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
            return $url = "http://" . $url;
        } else {
            return $url;
        }
    }

    /**
     * @param $date
     * Format date as ago
     */
    public static function formatDateAgo($date)
    {
        if ($date) {
            return Carbon::createFromTimestamp(strtotime($date))->diffForHumans();
        } else {
            return $date;
        }
    }

    /**
     * Format Date
     * @param $date
     * @return formatted date
     */
    public static function formatDate($date, $not_available = true)
    {
        if ($date) {
            return date(config('app.date_format_php'), strtotime($date));
        } else {
            if ($not_available == false) {
                return '';
            }
            return '';
        }
    }

    /**
     * Format Date
     * @param $date
     * @return formatted date
     */
    public static function formatDateTime($date, $not_available = true)
    {
        if ($date) {
            return date(config('app.date_time_format_php'), strtotime($date));
        } else {
            if ($not_available == false) {
                return null;
            }
            return null;
        }
    }

    /**
     * Show error page
     * @return \Illuminate\Http\Response
     */
    public static function showErrorPage()
    {
        return response()->view('errors.error', [], 500);
    }

    public static function convertFilterDate($keyword)
    {
        $date = '';
        try {
            if (\Carbon\Carbon::createFromFormat(config('app.date_format_php'), $keyword) !== false) {
                $date = self::convertDate($keyword);
            }
        } catch (Exception $e) {
            $date = '';
        }
        return $date;
    }

    /**
     * function for display amount
     * @param $amount
     * @return int|string
     */
    public static function currency($amount)
    {
        if ($amount == '') {
            $amount = 0;
            $amount = setting('default_currency_code').' '.number_format($amount, 2);
        } elseif ($amount < 0) {
            $amount = -$amount;
            $amount = '-' . setting('default_currency_code').' '.number_format($amount, 2);
        } else {
            $amount = setting('default_currency_code').' '.number_format($amount, 2);
        }
        return $amount;
    }

    /**
     * function for remove .00 from amount
     * @param $price
     * @return bool|string
     */
    public static function formatDBAmount($price)
    {
        $price = substr($price, -3) == ".00" ? substr($price, 0, -3) : $price;
        return $price;
    }

    /**
     * function for delete directory
     * @param $dir
     * @return bool
     */
    public static function deleteDirectory($dir)
    {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '' || $item == '.gitignore') {
                continue;
            }

            if (!self::deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }

        return rmdir($dir);
    }

    /**
     * Function to generate randomkey
     * @param NA
     * @return random key
     */
    public static function generateRandomKey()
    {
        $salt = substr(sha1(uniqid(mt_rand(), true)), 0, 4);
        return substr(sha1($salt) . $salt, 5, 15);
    }

    /**
     * function for generate new file name
     * @param NA
     * @return Generated new file name
     */
    public static function generateuDynamicFileName()
    {
        return substr(self::generateRandomKey(), 0, 4) . '-' . substr(self::generateRandomKey(), 0, 4) . '-' . substr(self::generateRandomKey(), 0, 4) . '-' . substr(self::generateRandomKey(), 0, 4);
    }

    public static function onlyTwoDecimal($foo)
    {
        return number_format((float) $foo, 2, '.', '');
    }

    public static function getCodetype($val = null)
    {
        if (isset($val) && !empty($val)) {
            $val = trim($val);
            $conf = config('constant.discountCodeType');
            foreach ($conf as $key => $value) {
                if ($val == $value) {
                    return $key;
                }
            }
        }
    }

    public static function showCodeTypeValInFormate($val = null, $amount = null)
    {
        if (isset($val) && !empty($val)) {
            $val = trim($val);
            if ($val == 1) {
                return $amount . ' %';
            }
            if ($val == 2) {
                return static::currency($amount);
            }
        }
    }

    public static function getExtendetimeLine($val = null)
    {
        if (isset($val) && !empty($val)) {
            $val = trim($val);
            $conf = config('constant.extend_delivery');
            foreach ($conf as $key => $value) {
                if ($val == $value) {
                    return $key;
                }
            }
        }
    }

    /**
     * function for desciption popup
     * @param $str word count
     * @param $id saperator id
     * @return Generated new file name
     */

    public static function decriptionPopup($str, $id)
    {
        if (str_word_count(trim($str)) > 5) {
            return implode(' ', array_slice(explode(' ', $str), 0, 5)) . '..<a href="#" style="color:blue;" data-toggle="modal" data-target="#declaraion_heading_' . $id . '">Read more</a>';
        } else {
            if (strlen($str) > 80) {
                return substr($str, 0, 80) . '..<a href="#" style="color:blue;" data-toggle="modal" data-target="#declaraion_heading_' . $id . '">Read more</a>';
            }
            return $str;
        }
    }


    function sendSms($msg, $to){
        $authKey = "API707295347009";
        $password = "trRpBKVoFi";
        $senderID = "Dome-Water";
        $mobileNumber = $to;
        $message = urlencode($msg);
        $url = "http://api.smsala.com/api/SendSMS?api_id=".$authKey."&api_password=".$password."&sms_type=T&encoding=T&sender_id=".$senderID."&phonenumber=974".$mobileNumber."&textmessage=".$message;
        $output = file_get_contents($url);
        return $output;
    }

    
    /**
     * function for send push notification
     * @param $id device token
     * @param $msg message tobe sent for push notification 
     * @param $title title 
     * @param $key key for API
     * @return Generated new file name
     */
    public static function sendNotification($id,$msg,$title,$key,$img=false){  
        $url = "https://fcm.googleapis.com/fcm/send";
        $notification = array(
            'title' =>$title ,
            'body' => $msg,
            'sound' => 'default',
            'badge' => '1',
            'image'=>$img
        );

        $data = array(
            'title' =>$title ,
            'body' => $msg,
            'sound' => 'default',
            'badge' => '1',
            'image'=>$img
        );

        $arrayToSend = array(
            'to' => $id,
            'notification' => $notification,
            'priority'=>'high',
            'data' => $data,
        );
        $json = json_encode($arrayToSend);
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: key='. $key;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);  
    }
    /*
        check an create slug
    */
    function createUsername($first_name,$whr=0){
        $slug = Str::slug($title, '-');
        $slug = strtolower($slug);
        $slugExist = Collection::where(DB::raw('LOWER(slug)'),$slug)->where('id','!=',$whr)->get();
        if(count($slugExist)){
            $slug = Str::slug($title.'-'.Str::random(5).'-'.Str::random(5), '-');
            return $slug;
        }else{
            return $slug;
        }
    }
}
