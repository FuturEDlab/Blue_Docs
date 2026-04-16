<?php
	/* Pull user session data via Neon data API. */
	if (isset($_COOKIE['__Secure-neon-auth_session_token'])) {
		/* Set Auth URL */
		$url = $_ENV['BLUE_DOCS_NEON_AUTH_BASE_URL'] . '/get-session';

		/* Construct Headers */
		$headers = [
			'Accept: application/json',
			'Origin: ' . $_ENV['VERCEL_ORIGIN']
		];

		/* Initialize cURL */
		$ch = curl_init();

		/* Set cURL Options */
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_COOKIE, '__Secure-neon-auth.session_token=' . $_COOKIE['__Secure-neon-auth_session_token']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		/* Handle cURL Response */
		$response = curl_exec($ch);
		if ($response === false) {
			die('cURL Error: ' . curl_error($ch));
		} else {
			$userSession = json_decode($response, true);
		}
	} else {
		header('Location: /login');
		exit;
	}
?>

<?php
	/* Pull user's document information via SQL connection. */

	/* Set SQL Settings */
	$host = $_ENV['BLUE_DOCS_PGHOST'];
	$port = $_ENV['BLUE_DOCS_PGPORT'] ?? 5432;
	$dbname = $_ENV['BLUE_DOCS_PGDATABASE'];
	$user = $_ENV['BLUE_DOCS_PGUSER'];
	$password = "endpoint=" . $_ENV['BLUE_DOCS_NEON_PROJECT_ID'] . ";" . $_ENV['BLUE_DOCS_PGPASSWORD'];
	//$options = [ endpoint => $_ENV['NEON_PROJECT_ID'] ];

	try {
		/* Set Connection Details */
		$dbInfo = sprintf("pgsql:host=%s;port=%d;dbname=%s;sslmode=require", $host, $port, $dbname);
		$pdo = new PDO($dbInfo, $user, $password/*, $options*/);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		/* Set SQL Query */
		$stmt = $pdo->query('SELECT name, description, author, date_created, user_uploaded FROM markdown_files');
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

		/* Set SQL Query */
		$stmt = $pdo->query('SELECT default_language, default_os, default_engine FROM user_settings WHERE user_id = \'' . $userSession['user']['id'] . '\' LIMIT 1');
		$userSettingsResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

	} catch (PDOException $e) {
		die("Database connection failed: " . $e->getMessage());
	}
?>

<!DOCTYPE HTML>
<HTML class="h-full w-full">
	<head>
		<meta charset="UTF-8">
		<title>Blue Docs</title>

		<link href="/output.css" rel="stylesheet" type="text/css">
		<script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>
		<script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>
	</head>

	<body class="flex flex-col justify-start h-full md:flex-row">
		<search class="flex flex-row w-full md:w-1/5 md:flex-col">
			<div class="p-16 w-1/8 md:w-full">
				<a href="/index"><img src="/templogo.svg" alt="Temporary Blue Docs Logo"></a>
			</div>

			<div class="flex flex-row gap-5 mx-14 my-4 md:flex-col">
				<label for="accountSelect" class="select-none relative cursor-pointer text-lg rounded-lg p-3 border border-4 border-transparent has-checked:bg-sky-200 has-checked:border-sky-400">
					<input autocomplete="off" checked type="radio" onclick="contentSelect('account')" id="accountSelect" name="accountContent" class="cursor-pointer absolute appearance-none inset-0" required/>
					My Account
				</label>

				<label for="documentsSelect" class="select-none relative cursor-pointer text-lg rounded-lg p-3 border border-4 border-transparent has-checked:bg-sky-200 has-checked:border-sky-400">
					<input autocomplete="off" type="radio" onclick="contentSelect('documents')" id="documentsSelect" name="accountContent" class="cursor-pointer absolute appearance-none inset-0" required/>
					My Documents
				</label>

				<label for="notificationsSelect" class="select-none relative cursor-pointer text-lg rounded-lg p-3 border border-4 border-transparent has-checked:bg-sky-200 has-checked:border-sky-400">
					<input autocomplete="off" type="radio" onclick="contentSelect('notifications')" id="notificationsSelect" name="accountContent" class="cursor-pointer absolute appearance-none inset-0" required/>
					Notifications
				</label>

				<label for="privacySelect" class="select-none relative cursor-pointer text-lg rounded-lg p-3 border border-4 border-transparent has-checked:bg-sky-200 has-checked:border-sky-400">
					<input autocomplete="off" type="radio" onclick="contentSelect('privacy')" id="privacySelect" name="accountContent" class="cursor-pointer absolute appearance-none inset-0" required/>
					Privacy
				</label>

				<label for="feedbackSelect" class="select-none relative cursor-pointer text-lg rounded-lg p-3 border border-4 border-transparent has-checked:bg-sky-200 has-checked:border-sky-400">
					<input autocomplete="off" type="radio" onclick="contentSelect('feedback')" id="feedbackSelect" name="accountContent" class="cursor-pointer absolute appearance-none inset-0" required/>
					Feedback
				</label>
			</div>
		</search>

		<main class="w-full px-6 py-20 md:w-4/5">

			<!-- Account Tab -->
			<form id="account" autocomplete="off" class="flex flex-col justify-between h-full overflow-y-auto">
				<h1 class="text-xl">My Account</h1>

				<div>
					<div class="flex flex-row p-2 pt-0">
						<h1 id='userImage' class="select-none text-5xl">
							<?php
								if ($userSession['user']['image']) {
									echo $userSession['user']['image'];
								} else {
									echo '⚓️'; // Unecessary if we set the image by default when the account is created.
								}
							?>
						<h1>
						<div class="flex flex-col">
							<?php
								echo $userSession['user']['name'];
							?>
							<el-dropdown class="float-right">

								<!-- Edit image clickable -->
								<button class="cursor-pointer text-blue-500">Edit display image</button>

								<!-- Dropdown Contents -->
								<el-menu anchor="bottom" popover>
									<div class="mt-2 p-2rounded-md border-2">
										<emoji-picker class="light"></emoji-picker>
										<input type="text">
									</div>
								</el-menu>

							</el-dropdown>
						</div>
					</div>
				</div>

				<hr class="border-gray-500 border-2">

				<div class="flex flex-row">
					<div class="w-1/2 flex flex-col">
						<h1 class="text-xl font-bold">About</h1>
						<h2 class="text-md font-bold">First Name:</h2>
						<input id="firstname" type="text" name="firstname" value="<?php echo substr($userSession['user']['name'], 0, strpos($userSession['user']['name'], ' ')) ?>" placeholder="First Name" class="m-1 block w-1/2 float-left rounded-sm outline-2 px-1 bg-sky-200 outline-sky-400"/>
						<h2 class="text-md font-bold">Last Initial:</h2>
						<input id="lastinitial" type="text" name="lastinitial" value="<?php echo substr($userSession['user']['name'], -1) ?>" maxlength="1" placeholder="Last Initial" class="m-1 block w-1/2 float-left rounded-sm outline-2 px-1 bg-sky-200 outline-sky-400"/>
						<h2 class="text-md font-bold">Email:</h2>
						<?php
							echo $userSession['user']['email'];
						?>
						<h2 class="text-md font-bold">Role:</h2>
						<?php
							echo $userSession['user']['role'];
						?>
					</div>

					<div class="w-1/2 flex flex-col">
						<h1 class="text-xl font-bold">My Setup</h1>
						<h2 class="text-md font-bold">Default Language:</h2>
							<select id="defaultlanguage" class="m-1 block w-1/2 float-left rounded-sm outline-2 px-1 bg-sky-200 outline-sky-400">
								<option>Select a language</option>
								<option>C</option>
								<option>C#</option>
								<option>C++</option>
								<option>Go</option>
								<option>Java</option>
								<option>JavaScript</option>
								<option>Kotlin</option>
								<option>PHP</option>
								<option>Python</option>
								<option>Ruby</option>
								<option>Rust</option>
								<option>Swift</option>
								<option>TypeScript</option>
							</select>
						<h2 class="text-md font-bold">Default OS:</h2>
							<select id="defaultos" class="m-1 block w-1/2 float-left rounded-sm outline-2 px-1 bg-sky-200 outline-sky-400">
								<option>Select an OS</option>
								<option>Linux</option>
								<option>Mac OS</option>
								<option>Windows</option>
							</select>
						<h2 class="text-md font-bold">Default Game Engine:</h2>
							<select id="defaultengine" class="m-1 block w-1/2 float-left rounded-sm outline-2 px-1 bg-sky-200 outline-sky-400">
								<option>Select a game engine</option>
								<option>Godot</option>
								<option>Unity</option>
								<option>Unreal</option>
							</select>
						</div>
					</div>

					<button id="accountChange" type="submit" disabled class="w-1/6 ml-1 mb-2 p-5 rounded-sm bg-sky-300 outline-sky-500 outline-2 hover:bg-sky-200 hover:outline-sky-400 focus:bg-sky-300 focus:outline-sky-500 enabled:cursor-pointer disabled:bg-gray-300 disabled:outline-gray-500">
						Save Changes
					</button>

					<hr class="border-gray-500 border-2">

					<h1 class="text-xl font-bold">Account Management</h1>

					<div class="flex p-4">
						<div>
							<h2 class="text-lg">Download All Document Data</h2>
							<p class="text-sm">Download a copy of all documents uploaded.</p>
						</div>

						<button disabled type="button" class="enabled:cursor-pointer ml-auto p-3 rounded-sm bg-sky-300 outline-sky-500 outline-2 hover:bg-sky-200 hover:outline-sky-400 focus:bg-sky-300 focus:outline-sky-500 disabled:bg-gray-300 disabled:outline-gray-500">Download Data</button>
					</div>

					<div class="flex border-2 border-red-500 rounded-sm p-4">
						<div>
							<h2 class="text-lg">Delete Account</h2>
							<p class="text-sm">Permanently delete your account.</p>
						</div>

						<button command="show-modal" commandfor="deleteAccountPopup" type="button" class="cursor-pointer ml-auto p-3 rounded-sm bg-red-300 outline-red-500 outline-2 hover:bg-red-200 hover:outline-red-400 focus:bg-red-300 focus:outline-red-500">Delete Account</button>
						<!-- Delete Account Popup -->
						<el-dialog>
							<dialog id="deleteAccountPopup" class="fixed inset-0 size-auto max-h-none max-w-none bg-transparent backdrop:bg-transparent">
								<el-dialog-backdrop class="fixed inset-0 bg-gray-900/50 transition-opacity data-closed:opacity-0 data-enter:duration-300 data-enter:ease-out data-leave:duration-200 data-leave:ease-in"></el-dialog-backdrop>

								<div tabindex="0" class="flex min-h-full items-center justify-center p-4 text-center">
									<el-dialog-panel class="relative transform overflow-hidden rounded-lg border-3 border-red-500 bg-white text-left p-4">
										<button type="button" command="close" commandfor="deleteAccountPopup" class="absolute right-4 cursor-pointer ml-auto font-bold hover:text-gray-200 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500">
											<span class="sr-only">Close Account Deletion Popup</span>
											X
										</button>

										<div class="flex flex-col gap-3 items-center">
											<h1 class="text-3xl font-bold text-red-500">Confirm Account Deletion</h2>
											<h2 class="text-lg">Are you sure you want to delete your account? If so, confirm by typing your email below:</h2>
											<input id="deleteEmail" type="text" class="w-3/4 p-2 border-black border-3">
											<button disabled id="deleteAccountSubmit" type="button" onclick="deleteAccount()" class="enabled:cursor-pointer w-1/4 rounded-md bg-red-400 hover:bg-red-300 focus:bg-red-400 p-2 disabled:bg-gray-300">Delete Account</button>
										</div>
									</el-dialog-panel>
								</div>
							</dialog>
						</el-dialog>
					</div>
			</form>

			<!-- Documents Tab -->
			<div id="documents" hidden>
				<h1 class="text-xl mb-3">Documents</h1>

				<div>
					<h2 class="text-md font-bold">Recently Viewed</h2>

					<div class="relative">
						<div class="absolute pointer-events-none bg-linear-[to_right,white,transparent_0.75%,transparent_99.25%,white] h-full w-full"></div>
						<div class="overflow-x-auto grid grid-flow-col grid-rows-1 py-4">
							<?php
								echo
								'<div class="w-sm flex flex-col gap-1 border-2 border-dashed p-6 m-2 rounded-lg h-39 opacity-55">
									<h3 class="text-lg font-bold">You don\'t have any recently viewed documents.</h3>
								</div>'
							?>
						</div>
					</div>
				</div>

				<div>
					<h2 class="text-md font-bold">Bookmarked</h2>

					<div class="relative">
						<div class="absolute pointer-events-none bg-linear-[to_right,white,transparent_0.75%,transparent_99.25%,white] h-full w-full"></div>
						<div class="overflow-x-auto grid grid-flow-col grid-rows-1 py-4">
							<?php
								echo
								'<div class="w-sm flex flex-col gap-1 border-2 border-dashed p-6 m-2 rounded-lg h-39 opacity-55">
									<h3 class="text-lg font-bold">You don\'t have any bookmarks.</h3>
								</div>'
							?>
						</div>
					</div>
				</div>

				<div>
					<h2 class="text-md font-bold">My Documentation</h2>

					<div class="relative">
						<div class="absolute pointer-events-none bg-linear-[to_right,white,transparent_0.75%,transparent_99.25%,white] h-full w-full"></div>
						<div class="overflow-x-auto grid grid-flow-col grid-rows-1 py-4">
							<?php
								$listEmpty = true;
								foreach ($results as $document) {
									if ($userSession['user']['id'] == $document['user_uploaded']) {
										echo
										'<a href="#" class="w-sm flex flex-col gap-1 bg-sky-400 p-6 m-2 rounded-lg h-39">
											<h3 class="text-lg font-bold">' . $document['name'] . '</h3>
											<div>
												<p class="text-xs truncate">Author: <span class="authorName">' . $document['author'] . '</span></p>
												<p class="text-xs">Date Created: <span class="dateCreated">' . substr($document['date_created'], 0, strpos($document['date_created'], ' ')) . '<em hidden>' . substr($document['date_created'], strpos($document['date_created'], ' ')) . '</em></span></p>
											</div>
											<p class="text-sm line-clamp-2">' . $document['description'] . '</p>
										</a>';
										$listEmpty = false;
									}
								}
								if ($listEmpty) {
									echo
									'<div class="w-sm flex flex-col gap-1 border-2 border-dashed p-6 m-2 rounded-lg h-39 opacity-55">
										<h3 class="text-lg font-bold">You don\'t have any uploaded documents.</h3>
									</div>';
								}
							?>
						</div>
					</div>
				</div>
			</div>

			<!-- Notifications Tab -->
			<div id="notifications" hidden>
				Example Page
			</div>

			<!-- Privacy Tab -->
			<div id="privacy" hidden>
				Example Page
			</div>

			<!-- Feedback Tab -->
			<form id="feedback" hidden autocomplete="off" class="flex flex-col gap-3 h-full overflow-y-auto">
				<h1 class="text-xl">Feedback</h1>

				<h2 class="text-md font-bold">Name:</h2>
				<input disabled id="feedbackName" type="text" placeholder="If anonymous, may leave blank" class="m-1 block w-1/4 float-left rounded-sm outline-2 px-1 bg-sky-200 outline-sky-400 disabled:bg-gray-300 disabled:outline-gray-500"/>
				
				<h2 class="text-md font-bold">Email:</h2>
				<input disabled id="feedbackEmail" type="text" placeholder="If anonymous, may leave blank" class="m-1 block w-1/4 float-left rounded-sm outline-2 px-1 bg-sky-200 outline-sky-400 disabled:bg-gray-300 disabled:outline-gray-500"/>

				<h2 class="text-md font-bold">Feedback Type: (required)</h2>
				<select disabled id="feedbackType" class="m-1 block w-1/4 float-left rounded-sm outline-2 px-1 bg-sky-200 outline-sky-400 disabled:bg-gray-300 disabled:outline-gray-500">
					<option>Report an issue/bug</option>
					<option>General feedback</option>
					<option>Feature requests</option>
				</select>

				<label for="message" class="text-md font-bold">Message: (required)</label>
				<textarea disabled id="message" name="message" rows="4" cols="50" class="m-1 block w-1/2 float-left rounded-sm outline-2 px-1 bg-sky-200 outline-sky-400 disabled:bg-gray-300 disabled:outline-gray-500"></textarea>

				<button id="feedbackSubmit" type="submit" disabled class="w-1/5 ml-1 my-2 p-5 rounded-sm bg-sky-300 outline-sky-500 outline-2 hover:bg-sky-200 hover:outline-sky-400 focus:bg-sky-300 focus:outline-sky-500 enabled:cursor-pointer disabled:bg-gray-300 disabled:outline-gray-500">
					Submit
				</button>
			</form>
		</main>
	</body>

	<script>
		/* Set default user setting select dropdowns and script load. */
		window.onload = function() {
			document.getElementById('defaultlanguage').value = '<?php echo $userSettingsResults[0]['default_language'] ?>';
			document.getElementById('defaultos').value = '<?php echo $userSettingsResults[0]['default_os'] ?>';
			document.getElementById('defaultengine').value = '<?php echo $userSettingsResults[0]['default_engine'] ?>';
		};
	</script>

	<script>
		/* Apply event listeners. */

		/* Add listeners to check if any settings deviate from previous values. */
		const accountInputs = [...document.getElementById('account').querySelectorAll('input, select')];
		accountInputs.forEach((input) => {
            input.addEventListener('input', (event) => {
				toggleSubmit();
			});
        });

		/* Call to update the user's attributes on form submission. */
		document.getElementById('account').addEventListener('submit', (event) => {
			event.preventDefault();

			/* Construct JSON Data */
			const userAttributes = {
				'firstname': '<?php echo substr($userSession['user']['name'], 0, strpos($userSession['user']['name'], ' ')) ?>',
				'lastinitial': '<?php echo substr($userSession['user']['name'], -1) ?>',
				'image': '<?php echo $userSession['user']['image'] ?? 'null' ?>',
				'defaultlanguage': '<?php echo $userSettingsResults[0]['default_language'] ?>',
				'defaultos': '<?php echo $userSettingsResults[0]['default_os'] ?>',
				'defaultengine': '<?php echo $userSettingsResults[0]['default_engine'] ?>'
			}
			let updateUserBody = { user_id: '<?php echo $userSession['user']['id'] ?>' }
			if (document.getElementById('firstname').value != userAttributes['firstname'] || document.getElementById('lastinitial').value != userAttributes['lastinitial']) { 
				updateUserBody.name = document.getElementById('firstname').value + ' ' + document.getElementById('lastinitial').value;
			}
			if (document.getElementById('userImage').innerText != userAttributes['image']) { updateUserBody.image = document.getElementById('userImage').innerText; }
			if (document.getElementById('defaultlanguage').value != userAttributes['defaultlanguage']) { updateUserBody.default_language = document.getElementById('defaultlanguage').value; }
			if (document.getElementById('defaultos').value != userAttributes['defaultos']) { updateUserBody.default_os = document.getElementById('defaultos').value; }
			if (document.getElementById('defaultengine').value != userAttributes['defaultengine']) { updateUserBody.default_engine = document.getElementById('defaultengine').value; }

			/* Make Fetch Call */
			fetch('/api/updateUser.php', {
				credentials: 'same-origin',
				method: 'POST',
				headers: {
					'Content-Type': 'application/json'
				},
				body: JSON.stringify(updateUserBody)
			})
			.then(response => {
				//location.reload();
			})
			.catch(error => {
				console.log(error.message);
			});
		});

		document.querySelector('emoji-picker').addEventListener('emoji-click', event => {
			document.getElementById('userImage').innerText = event.detail.unicode;
			toggleSubmit();
		});

		document.querySelector('emoji-picker').addEventListener('click', event => {
			event.stopPropagation();
		});

		document.getElementById('deleteEmail').addEventListener('input', (event) => {
			if (document.getElementById('deleteEmail').value == '<?php echo $userSession['user']['email'] ?>') {
				document.getElementById('deleteAccountSubmit').disabled = false;
			} else {
				document.getElementById('deleteAccountSubmit').disabled = true;
			}
		});
	</script>

	<script>
		/* Define Functions */
		const contentList = ['account', 'documents', 'notifications', 'privacy', 'feedback'];

		const userAttributes = {
			'firstname': '<?php echo substr($userSession['user']['name'], 0, strpos($userSession['user']['name'], ' ')) ?>',
			'lastinitial': '<?php echo substr($userSession['user']['name'], -1) ?>',
			'image': '<?php echo $userSession['user']['image'] ?? 'null' ?>',
			'defaultlanguage': '<?php echo $userSettingsResults[0]['default_language'] ?>',
			'defaultos': '<?php echo $userSettingsResults[0]['default_os'] ?>',
			'defaultengine': '<?php echo $userSettingsResults[0]['default_engine'] ?>'
		}

		function contentSelect(pageSelected) {
			for (const pageName of contentList) {
				if (pageSelected == pageName) {
					document.getElementById(pageName).hidden = false;
				} else {
					document.getElementById(pageName).hidden = true;
				}
			}
		}

		/* Call Neon API to Delete User Account */
		function deleteAccount() {
			fetch('/deleteUser', {
				credentials: 'same-origin',
				method: 'POST',
				headers: {
					'Content-Type': 'application/json'
				},
				body: JSON.stringify({id: '<?php echo $userSession['user']['id'] ?>'})
			})
			.then(response => {
				location.reload();
			})
			.catch(error => {
				console.log(error.message);
			});
		}

		function toggleSubmit() {
			if (document.getElementById('firstname').value != userAttributes['firstname'] ||
				document.getElementById('lastinitial').value != userAttributes['lastinitial'] ||
				document.getElementById('userImage').innerText != userAttributes['image'] ||
				document.getElementById('defaultlanguage').value != userAttributes['defaultlanguage'] ||
				document.getElementById('defaultos').value != userAttributes['defaultos'] ||
				document.getElementById('defaultengine').value != userAttributes['defaultengine']) {

				document.getElementById('accountChange').disabled = false;
			} else {
				document.getElementById('accountChange').disabled = true;
			}
		}
	</script>
</HTML>