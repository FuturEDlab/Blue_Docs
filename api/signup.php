<?php
	/* Pass sign-up request to Neon Auth and return the response. */
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		if (isset($_POST['email']) && isset($_POST['password']) && isset($_POST['firstname']) && isset($_POST['lastinitial'])) {
			$email = htmlspecialchars($_POST['email']);
			$password = htmlspecialchars($_POST['password']);
			$name = htmlspecialchars($_POST['firstname']) . ' ' . htmlspecialchars($_POST['lastinitial']);

			/* Set Auth URL */
			$url = $_ENV['BLUE_DOCS_NEON_AUTH_BASE_URL'] . '/sign-up/email';

			/* Construct Headers */
			$headers = [
				'Content-Type: application/json',
				'Accept: application/json',
				'Origin: ' . $_ENV['VERCEL_URL']
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
			curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			/* Handle cURL Response and End Session */
			$response = curl_exec($ch);
			if ($response === false) {
				echo json_encode(['curl_error' => curl_error($ch)]);
			} else {
				/* Return headers and body from Neon to client. */
				$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
				$header = substr($response, 0, $header_size);
				$body = substr($response, $header_size);

				$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				http_response_code($http_code);

				$header_lines = preg_split('/\r\n|\r|\n/', trim($header));
				foreach ($header_lines as $line) {
					/* Only pass along the set-cookie header. */
					if (stripos($line, 'Set-Cookie:') === 0) {
						header($line);
					}
				}
				echo $body;
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

		<link href="/output.css" rel="stylesheet" type="text/css">
		<link href="/loading.gif" rel="preload" as="image">
	</head>

	<body>
		<main class="flex flex-col items-center justify-center min-h-screen">

			<!-- Signup Container -->
			<div class="flex flex-col items-center justify-between w-1/2 lg:w-1/5 gap-7">

				<!-- Logo -->
				<a href="/api/index.php" class="w-full"><img src="/templogo.svg" alt="Temporary Blue Docs Logo"></a>

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
				<p class="font-bold">or log in <a href="/login" class="underline">here</a>!</p>

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
							errorSpan.innerHTML = '<img src="/loading.gif" alt="Loading GIF" class="size-6">';
							fetch('/signup', {
								method: 'POST',
								headers: {
									'Content-Type': 'application/x-www-form-urlencoded'
								},
								body: new URLSearchParams(formData).toString()
							})
							.then(response => {
								if (response.status >= 500) {
									errorSpan.textContent = "Server error.";
								} else {
									return response.json();
								}
							})
							.then(data => {
								if (data !== undefined) {
									if ('user' in data) {
										window.location.href='/verification';
									} else {
										errorSpan.textContent = data.message;
									}
								}
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
