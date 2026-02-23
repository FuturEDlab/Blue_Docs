<?php
	/* Pass OTP submission request to Neon Auth and return the response. */
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		$jsonData = json_decode(file_get_contents('php://input'), true);
		if (isset($jsonData['otp'])) {
			$email = htmlspecialchars($_COOKIE['email']);
			$otp = htmlspecialchars($jsonData['otp']);

			/* Set Auth URL */
			$url = $_ENV['PREVIEW_NEON_AUTH_BASE_URL'] . '/email-otp/verify-email';

			/* Construct Headers */
			$headers = [
				'Content-Type: application/json',
				'Accept: application/json',
				'Origin: ' . $_ENV['VERCEL_URL']
			];

			/* Construct JSON Data */
			$data = [
				'email' => $email,
				'otp' => $otp
			];

			/* Initialize cURL */
			$ch = curl_init();

			/* Set cURL Options */
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			/* Handle cURL Response */
			$response = curl_exec($ch);
			if ($response === false) {
				die('cURL Error: ' . curl_error($ch));
			} else {
				$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				http_response_code($http_code);
				echo $response;
			}

		} else {
			/* Respond 401 if an email and password isn't received. */
			http_response_code(401);
			$response = ['message' => 'There was an issue fetching the email verification info.'];
			echo json_encode($response);
		}
		exit;

	/* Return if not a redirected GET request or POST Request */
	} else if ($_SERVER['REQUEST_METHOD'] != 'GET' || empty($_SERVER['HTTP_REFERER']) || !isset($_COOKIE['email'])) {
		http_response_code(405);
		exit('Method not allowed.');
	}
?>

<!DOCTYPE HTML>
<HTML>
	<head>
		<meta charset="UTF-8">
		<title>Blue Docs Login</title>

		<link href="/output.css" rel="stylesheet" type="text/css">
		<link href="/loading.gif" rel="preload" as="image">
	</head>

	<body>
		<main class="flex flex-col items-center justify-center min-h-screen">

			<!-- Login Return Button -->
			<button id="loginReturn" onclick="window.location.href='/login'" class="absolute top-10 left-10 w-1/4 lg:w-1/10 rounded-sm font-bold text-white bg-sky-500 outline-sky-500 outline-2 hover:bg-sky-400 hover:outline-sky-400 focus:bg-sky-500 focus:outline-sky-500 p-1">Back to log in</button>

			<!-- Verification Container -->
			<div class="flex flex-col items-center justify-between w-2/3 lg:w-1/3 gap-2">

				<!-- Title -->
				<h1 class="text-4xl font-bold">Verification code</h1>

				<!-- Verification Message -->
				<p class="text-lg text-center">A verification code has been sent to <?php echo substr_replace($_COOKIE['email'], str_repeat('*', strlen($_COOKIE['email']) - 7), 3, strlen($_COOKIE['email']) - 7) ?>.
				<br>Please check your email and enter the code below:</p>

				<!-- Verification Form -->
				<form class="flex flex-col items-center justify-between w-full gap-7" id="otpForm">
					<span id="errorSpan" class="text-red-500 text-center"></span>

					<!-- OTP Input -->
					<div class="flex items-center justify-center gap-3">
						<input type="text" pattern="\d*" maxlength="1" class="text-3xl text-bold text-center w-15 h-20 p-4 rounded-md bg-sky-200 border border-sky-400 border-3 focus:outline-none"/>
						<input type="text" pattern="\d*" maxlength="1" class="text-3xl text-bold text-center w-15 h-20 p-4 rounded-md bg-sky-200 border border-sky-400 border-3 focus:outline-none"/>
						<input type="text" pattern="\d*" maxlength="1" class="text-3xl text-bold text-center w-15 h-20 p-4 rounded-md bg-sky-200 border border-sky-400 border-3 focus:outline-none"/>
						<input type="text" pattern="\d*" maxlength="1" class="text-3xl text-bold text-center w-15 h-20 p-4 rounded-md bg-sky-200 border border-sky-400 border-3 focus:outline-none"/>
						<input type="text" pattern="\d*" maxlength="1" class="text-3xl text-bold text-center w-15 h-20 p-4 rounded-md bg-sky-200 border border-sky-400 border-3 focus:outline-none"/>
						<input type="text" pattern="\d*" maxlength="1" class="text-3xl text-bold text-center w-15 h-20 p-4 rounded-md bg-sky-200 border border-sky-400 border-3 focus:outline-none"/>
					</div>

					<!-- Resend Link -->
					<p class="font-bold">Didn't get a code? <button type="button" onclick="resendOTP()" class="cursor-pointer text-sky-400">Resend</button></p>

					<!-- Code Submission Button -->
					<button id="otpSubmit" type="submit" disabled class="block w-half h-20 p-5 rounded-sm bg-sky-200 outline-sky-400 outline-2 hover:bg-sky-100 hover:outline-sky-300 focus:bg-sky-200 focus:outline-sky-400 disabled:bg-gray-300 disabled:outline-gray-500">Verify Code</button>
				</form>
			</div>
		</main>
	</body>

	<script>
		const errorSpan = document.getElementById('errorSpan');

		/* Handle OTP Submission */
		document.getElementById('otpForm').addEventListener('submit', (event) => {
			event.preventDefault();
			const verificationDigits = [...document.getElementById('otpForm').querySelectorAll('input[type=text]')];

			let otpCode = '';
			for (const digit of verificationDigits) {
				if (/\d/.test(digit.value)) {
					otpCode += digit.value;
				}
			}
			
			if (otpCode.length == 6) {
				errorSpan.innerHTML = '<img src="/loading.gif" alt="Loading GIF" class="size-6">';
				fetch('/verification', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json'
					},
					body: JSON.stringify({ otp: otpCode })
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
							window.location.href='/index';
						} else {
							errorSpan.textContent = data.message;
						}
					}
				})
				.catch(error => {
					errorSpan.textContent = error.message;
				});
			} else {
				errorSpan.textContent = 'Verification code not complete.';
			}
		});

		/* Handle Email Resend Request */
		function resendOTP() {
			errorSpan.innerHTML = '<img src="/loading.gif" alt="Loading GIF" class="size-6">';
			fetch('/resend', {
				method: 'POST',
				credentials: 'same-origin'
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
					errorSpan.textContent = data.message;
				}
			})
			.catch(error => {
				errorSpan.textContent = error.message;
			});
		}

		function resetEmail() {
			document.getElementById('email').setCustomValidity('');
			errorSpan.textContent = '';
		}
	</script>

	<script>
		const verificationDigits = [...document.getElementById('otpForm').querySelectorAll('input[type=text]')];

		/* Handle Key Input */
		const handleKeyDown = (e) => {
			if (!/^[0-9]{1}$/.test(e.key) && e.key !== 'Backspace' && e.key !== 'Delete' && e.key !== 'Tab' && !e.metaKey) {
				e.preventDefault();
			}

			if (e.key === 'Delete' || e.key === 'Backspace') {
				const index = verificationDigits.indexOf(e.target);
				if (verificationDigits[index].value != '') {
					verificationDigits[index].value = '';
					document.getElementById('otpSubmit').disabled = true;
				} else if (index > 0) {
					verificationDigits[index - 1].value = '';
					verificationDigits[index - 1].focus();
				}
			}
		}

		/* Handle Input Update */
		const handleInput = (e) => {
			const { target } = e;
			const index = verificationDigits.indexOf(target)
			if (target.value) {
				if (index < verificationDigits.length - 1) {
					verificationDigits[index + 1].focus();

					let complete = true;
					for (const digit of verificationDigits) {
						if (digit.value == '') {
							complete = false;
							break;
						}
					}
					if (complete) {
						document.getElementById('otpSubmit').disabled = false;
					}
				} else {
					document.getElementById('otpSubmit').disabled = false;
					verificationDigits[index].blur();
				}
			}
		}

		/* Handle Focus */
		const handleFocus = (e) => {
			e.target.select();
		}

		/* Handle Paste */
		const handlePaste = (e) => {
			e.preventDefault();
			const text = e.clipboardData.getData('text');
			if (!new RegExp(`^[0-9]{${verificationDigits.length}}$`).test(text)) {
				return;
			}
			const digits = text.split('');
			verificationDigits.forEach((input, index) => input.value = digits[index]);
		}

		verificationDigits.forEach((input) => {
            input.addEventListener('input', handleInput);
            input.addEventListener('keydown', handleKeyDown);
            input.addEventListener('focus', handleFocus);
            input.addEventListener('paste', handlePaste);
        })
	</script>
</HTML>
