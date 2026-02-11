<?php
	/* Only required before link to Neon */
	$_ENV = [ 'NEON_AUTH_BASE_URL' => 'https://httpbin.org/post' ];

	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		header('Content-Type: application/json');
		if (isset($_POST['email']) && isset($_POST['password']) && isset($_POST['firstname']) && isset($_POST['lastinitial'])) {
			$email = htmlspecialchars($_POST['email']);
			$password = htmlspecialchars($_POST['password']);
			$name = htmlspecialchars($_POST['firstname']) . ' ' . htmlspecialchars($_POST['lastinitial']);

			/* Set Auth URL */
			$url = $_ENV['NEON_AUTH_BASE_URL']/* . '/api/auth/sign-up/email'*/;

			/* Construct Headers */
			$headers = [
				'Content-Type: application/json',
				'Accept: application/json'
			];

			/* Construct JSON Data */
			$data = [
				'email' => $email,
				'password' => $password,
				'name' => $name
			];

			/* Initialize cURL */
			$ch = curl_init();

			/* Set cURL Options */
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			/* Handle cURL Response and End Session */
			$response = curl_exec($ch);
			if ($response === false) {
				die('cURL Error: ' . curl_error($ch));
			} else {
				$responseArray = json_decode($response);
				if (property_exists($responseArray->data, 'session')) {
					/* Respond 303 if authentication succeeds (Neon should create a session cookie). */
					http_response_code(303);
					$response = ['location' => 'index.php'];
					echo json_encode($response);
				} else {
					/* Respond 401 if authentication fails. */
					http_response_code(401);
					$response = ['message' => 'Sign up failed.'];
					echo json_encode($response);
				}
			}
		} else {
			/* Respond 401 if an email and password isn't received. */
			http_response_code(401);
			$response = ['message' => 'There was an issue fetching the sign up info.'];
			echo json_encode($response);
		}
		exit;

	/* Return if not a GET or POST Request */
	} else if ($_SERVER['REQUEST_METHOD'] != 'GET') {
		http_response_code(405);
		exit('Method not allowed.');
	}
?>

<!DOCTYPE HTML>
<HTML>
	<head>
		<meta charset="UTF-8">
		<title>Blue Docs Sign Up</title>

		<link href="/styles/output.css" rel="stylesheet" type="text/css">
		<link href="/public/loading.gif" rel="preload" as="image">
	</head>

	<body>
		<main class="flex flex-col items-center justify-center min-h-screen">

			<!-- Signup Container -->
			<div class="flex flex-col items-center justify-between w-1/5 gap-7">

				<!-- Logo -->
				<a href="/api/index.php" class="w-full"><img src="/public/templogo.svg" alt="Temporary Blue Docs Logo"></a>

				<!-- Signup Form -->
				<h1 class="">Sign up with a new account</h1>
				<form class="flex flex-col items-center justify-between w-full gap-4" id="signUpForm">
					<div class="w-full">
						<input id="firstname" type="text" name="firstname" autocomplete="firstname" placeholder="First Name" class="block w-47/100 float-left rounded-sm outline-2 px-1"/>
						<input id="lastinitial" type="text" name="lastinitial" autocomplete="lastinitial" maxlength="1" placeholder="Last Initial" class="block w-47/100 float-right rounded-sm outline-2 px-1"/>
					</div>
					<input id="email" type="text" name="email" autocomplete="email" placeholder="Email" onclick="resetForm()" class="block w-full rounded-sm outline-2 invalid:text-red-500 px-1"/>
					<input id="password" type="password" name="password" autocomplete="password" placeholder="Password" onclick="resetForm()" class="block w-full rounded-sm outline-2 invalid:text-red-500 px-1"/>
					<input id="confirmpassword" type="password" name="confirmpassword" autocomplete="confirmpassword" placeholder="Confirm Password" onclick="resetForm()" class="block w-full rounded-sm outline-2 invalid:text-red-500 px-1"/>
					<button type="submit" class="block w-full rounded-sm bg-sky-500 outline-sky-500 outline-2 hover:bg-sky-400 hover:outline-sky-400 focus:bg-sky-500 focus:outline-sky-500">Sign Up →</button>
					<span id="errorSpan" class="text-red-500 text-center"></span>
				</form>

				<!-- Login Link -->
				<p class="font-bold">or log in <a href="/api/login.php" class="underline">here</a>!</p>

			</div>
		</main>
	</body>

	<script>
		const errorSpan = document.getElementById('errorSpan');

		document.getElementById('signUpForm').addEventListener('submit', (event) => {
			event.preventDefault();

			const formData = new FormData(document.getElementById('signUpForm'));
			if (formData.get('email') && formData.get('password') && formData.get('confirmpassword') && formData.get('firstname') && formData.get('lastinitial')) {
				if (/.+@(mail.)?gvsu\.edu/.test(formData.get('email'))) {
					if (formData.get('password').length >= 8) {
						if (formData.get('password') === formData.get('confirmpassword')) {
							errorSpan.innerHTML = '<img src="/public/loading.gif" alt="Loading GIF" class="size-6">';
							fetch('signup.php', {
								method: 'POST',
								headers: {
									'Content-Type': 'application/x-www-form-urlencoded'
								},
								body: new URLSearchParams(formData).toString()
							})
							.then(response => response.json())
							.then(data => {
								errorSpan.textContent = data.message;
							})
							.catch(error => {
								errorSpan.textContent = error.message;
							});
						} else {
							document.getElementById('password').setCustomValidity('Password confirmation does not match.');
							document.getElementById('confirmpassword').setCustomValidity('Password confirmation does not match.');
							errorSpan.textContent = 'Password confirmation does not match.';
						}
					} else {
						document.getElementById('password').setCustomValidity('Password length is less than 8 characters.');
						errorSpan.textContent = 'Password length is less than 8 characters.';
					}
				} else {
					document.getElementById('email').setCustomValidity('Not a valid GVSU email.');
					errorSpan.textContent = 'Not a valid GVSU email.';
				}
			} else {
				errorSpan.textContent = 'Name, email, and password are required.';
			}
		});

		function resetForm() {
			document.getElementById('email').setCustomValidity('');
			document.getElementById('password').setCustomValidity('');
			document.getElementById('confirmpassword').setCustomValidity('');
			errorSpan.textContent = '';
		}
	</script>
</HTML>
