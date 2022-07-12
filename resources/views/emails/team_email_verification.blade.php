<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<title>Mailto</title>
<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700" rel="stylesheet">
<style type="text/css">
html { -webkit-text-size-adjust: none; -ms-text-size-adjust: none;}

@media only screen and (min-device-width: 750px) {
.table750 {width: 750px !important;}
}
@media only screen and (max-device-width: 750px), only screen and (max-width: 750px){
table[class="table750"] {width: 100% !important;}
.mob_b {width: 93% !important; max-width: 93% !important; min-width: 93% !important;}
.mob_b1 {width: 100% !important; max-width: 100% !important; min-width: 100% !important;}
.mob_left {text-align: left !important;}
.mob_soc {width: 50% !important; max-width: 50% !important; min-width: 50% !important;}
.mob_menu {width: 50% !important; max-width: 50% !important; min-width: 50% !important; box-shadow: inset -1px -1px 0 0 rgba(255, 255, 255, 0.2); }
.mob_center {text-align: center !important;}
.top_pad {height: 15px !important; max-height: 15px !important; min-height: 15px !important;}
.mob_pad {width: 15px !important; max-width: 15px !important; min-width: 15px !important;}
.mob_div {display: block !important;}
}
@media only screen and (max-device-width: 550px), only screen and (max-width: 550px){
.mod_div {display: block !important;}
}
.table750 {width: 750px;}
</style>
</head>
<body style="margin: 0; padding: 0;">

<table cellpadding="0" cellspacing="0" border="0" width="100%" style="background: #f3f3f3; min-width: 350px; font-size: 1px; line-height: normal;">
<tr>
<td align="center" valign="top">   			
<!--[if (gte mso 9)|(IE)]>
 <table border="0" cellspacing="0" cellpadding="0">
 <tr><td align="center" valign="top" width="750"><![endif]-->
<table cellpadding="0" cellspacing="0" border="0" width="750" class="table750" style="width: 100%; max-width: 750px; min-width: 350px; background: #f3f3f3;">
    <tr>
       <td class="mob_pad" width="25" style="width: 25px; max-width: 25px; min-width: 25px;">&nbsp;</td>
        <td align="center" valign="top" style="background: #ffffff;">

          <table cellpadding="0" cellspacing="0" border="0" width="100%" style="width: 100% !important; min-width: 100%; max-width: 100%; background: #f3f3f3;">
             <tr>
                <td align="right" valign="top">
                   <div class="top_pad" style="height: 25px; line-height: 25px; font-size: 23px;">&nbsp;</div>
                </td>
             </tr>
          </table>
          <table cellpadding="0" cellspacing="0" border="0" width="100%" style="width:100% !important; min-width: 100%; max-width: 100%; border-bottom:1px solid #eee; background:#427af1;">
             <tr>
                <td align="center" valign="top">
                   <div style="height: 15px; line-height: 15px; font-size: 37px;">&nbsp;</div>
                   <a href="#" target="_blank">
                      <img src="{{url('assets/img/logo.png')}}" alt="img" width="250" border="0" style="display: block; width: 250px;" />
                   </a>
                   <div style="height: 15px; line-height: 15px; font-size: 45px;">&nbsp;</div>
                </td>
             </tr>
          </table>
			<div style="height: 25px; line-height: 25px; font-size: 45px;">&nbsp;</div>
          <table cellpadding="0" cellspacing="0" border="0" width="88%" style="width: 88% !important; min-width: 88%; max-width: 88%;">
             <tr>
                <td align="left" valign="top">
                   <font face="'Source Sans Pro', sans-serif" color="#1a1a1a" style="font-size: 45px; line-height: 45px; font-weight: 300; letter-spacing: -1.5px;">
                      <span style="font-family: 'Source Sans Pro', Arial, Tahoma, Geneva, sans-serif; color: #1a1a1a; font-size: 38px; line-height: 38px; font-weight: 300; letter-spacing: -1.5px;">Hey {{$name}},</span>
                   </font>
                   <div style="height: 20px; line-height: 20px; font-size: 20px;">&nbsp;</div>
                   <font face="'Source Sans Pro', sans-serif" color="#585858" style="font-size: 22px; line-height: 30px;">
                      <span style="font-family: 'Source Sans Pro', Arial, Tahoma, Geneva, sans-serif; color: #585858; font-size: 18px; line-height: 24px;">{{$message_text}}</span>
                   </font>
                   @if(isset($sent_message) && $sent_message!='')
                   <div style="height: 20px; line-height: 20px; font-size: 20px;">&nbsp;</div>
                   <font face="'Source Sans Pro', sans-serif" color="#302e2e" style="font-size: 22px; line-height: 30px;">
                      <span style="font-family: 'Source Sans Pro', Arial, Tahoma, Geneva, sans-serif; color: #302e2e; font-size: 18px; line-height: 24px;font-weight: 600;">"{{$sent_message}}"</span>
                   </font>
                   @endif
				   @if(isset($url))
				   <table cellpadding="0" cellspacing="0" border="0" width="100%" style="width: 100% !important; max-width: 100%; min-width: 100%; border-radius: 4px;">
                      <tr>
                         <td align="center" valign="middle" height="55">
                            <a href="{{$url}}" target="_blank" style="display: inline-block; width: auto; height: 55px; font-family: 'Source Sans Pro', Arial, Verdana, Tahoma, Geneva, sans-serif; color: #ffffff; font-size: 20px; line-height: 55px; text-decoration: none; white-space: nowrap; font-weight: 600; background: #427af1; border-radius:5px; padding:0 15px;">
                                  <span style="font-family: 'Source Sans Pro', Arial, Verdana, Tahoma, Geneva, sans-serif; color: #ffffff; font-size: 20px; line-height: 55px; text-decoration: none; white-space: nowrap; width:120px; margin: auto; font-weight: 600;">{{$btn_text}}</span>
                            </a>
                         </td>
                      </tr>
                   </table>      
				   @endif             
                   <div style="height: 20px; line-height: 20px; font-size: 15px;">&nbsp;</div>
                    <font face="'Source Sans Pro', sans-serif" color="#585858" style="font-size: 24px; line-height: 32px;">
                      <span style="font-family: 'Source Sans Pro', Arial, Tahoma, Geneva, sans-serif; color: #585858; font-size: 18px; line-height: 24px;">Best wishes<br>The mortgagebox team</span>
                   </font>
                   <div style="height: 25px; line-height: 25px; font-size: 25px;">&nbsp;</div>
                </td>
             </tr>
          </table>
          <table cellpadding="0" cellspacing="0" border="0" width="100%" style="width: 100% !important; min-width: 100%; max-width: 100%; background: #f3f3f3;">
             <tr>
                <td align="center" valign="top">
                   <div style="height: 34px; line-height: 34px; font-size: 32px;">&nbsp;</div>
                   <table cellpadding="0" cellspacing="0" border="0" width="88%" style="width: 88% !important; min-width: 88%; max-width: 88%;">
                      <tr>
                         <td align="center" valign="top">
                            <font face="'Source Sans Pro', sans-serif" color="#868686" style="font-size: 14px; line-height: 20px;">
                               <span style="font-family: 'Source Sans Pro', Arial, Tahoma, Geneva, sans-serif; color: #868686; font-size: 14px; line-height: 20px;">Copyright &copy; {{date('Y')}} MortgageBox. All&nbsp;Rights&nbsp;Reserved. We&nbsp;appreciate&nbsp;you!</span>
                            </font>
                             
                             
                            <div style="height: 35px; line-height: 35px; font-size: 33px;">&nbsp;</div>
                         </td>
                      </tr>
                   </table>
                </td>
             </tr>
          </table>  

       </td>
       <td class="mob_pad" width="25" style="width: 25px; max-width: 25px; min-width: 25px;">&nbsp;</td>
    </tr>
 </table>
 <!--[if (gte mso 9)|(IE)]>
 </td></tr>
 </table><![endif]-->
</td>
</tr>
</table>
</body>
</html>
<!-- <!DOCTYPE html>
<html>
	<head>
		<title>Mortgagebox</title>
	</head>
	<body>
		<center>
			<h2 style="padding: 23px;background: #b3deb8a1;border-bottom: 6px green solid;">
				<a href="https://mbox2.technofox.co.in/">Visit Our Website : Mortgagebox</a>
			</h2>
		</center>
		<p>Hello {{$name}},</p>
		<p>{{$message_text}}</p>
		<strong>Thank you :)</strong>
	</body>
</html> -->


<!-- <!DOCTYPE html>
<html>
	<head>
		<title>Invite Team Member</title>
	</head>
	<body>
		<center>
			<h2 style="padding: 23px;background: #b3deb8a1;border-bottom: 6px green solid;">
				<a href="https://mbox2.technofox.co.in/">Visit Our Website : Mortgagebox</a>
			</h2>
		</center>
		<p>Hello {{$name}},</p>
		<p>{{$invited_by}} invites you to join the company.</p>
		<p>Please click the link below to join the team.</p>
		<a href="{{$url}}"><p>Confirm</p></a>
		<strong>Thank you :)</strong>
	</body>
</html> -->