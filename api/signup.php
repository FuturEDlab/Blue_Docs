<!DOCTYPE HTML>
<HTML>
	<head>
		<meta charset="UTF-8">
		<title>Blue Docs Sign Up</title>

		<link href="/styles/output.css" rel="stylesheet" type="text/css">
	</head>

	<body>
		<main class="flex flex-col items-center justify-center min-h-screen">

			<!-- Signup Container -->
			<div class="flex flex-col items-center justify-between w-1/5 gap-7">

				<!-- Logo -->
				<img src="/public/templogo.svg" alt="Temporary Blue Docs Logo" class="w-full">

				<!-- Signup Form -->
				<h1 class="">Sign up with a new account</h1>
				<form action="#" method="POST" class="flex flex-col items-center justify-between w-full gap-4">
					<div class="w-full">
						<input id="firstname" type="text" name="firstname" required autocomplete="firstname" placeholder="First Name" class="block w-47/100 float-left rounded-sm outline-4 px-1"/>
						<input id="lastinitial" type="text" name="lastinitial" required autocomplete="lastinitial" placeholder="Last Initial" class="block w-47/100 float-right rounded-sm outline-4 px-1"/>
					</div>
					<div class="w-full">
						<input id="email" type="text" name="email" required autocomplete="email" placeholder="Email" class="block w-full rounded-sm outline-4 px-1"/>
					</div>
					<div class="w-full">
						<input id="password" type="password" name="password" required autocomplete="password" placeholder="Password" class="block w-full rounded-sm outline-4 px-1"/>
					</div>
					<button type="submit" class="block w-full rounded-sm bg-sky-500 outline-sky-500 outline-3 hover:bg-sky-400 hover:outline-sky-400 focus:bg-sky-500 focus:outline-sky-500">Sign Up →</button>
				</form>

				<!-- Login Link -->
				<a href="/api/login.php" class="font-bold">or log in here!</a>

			</div>
		</main>
	</body>

	<!-- Dummy script to prevent rendering before loading CSS -->
	<script>0</script>
</HTML>
