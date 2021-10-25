<!DOCTYPE html>
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
		<p>Please click the link below to create your account and join the team.</p>
		<a href="{{$url}}"><p>Create Account</p></a>
		<strong>Thank you :)</strong>
	</body>
</html>