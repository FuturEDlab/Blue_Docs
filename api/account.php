<?php
	$host = $_ENV['PGHOST'];
	$port = $_ENV['PGPORT'] ?? 5432;
	$dbname = $_ENV['PGDATABASE'];
	$user = $_ENV['PGUSER'];
	$password = "endpoint=" . $_ENV['NEON_PROJECT_ID'] . ";" . $_ENV['PGPASSWORD'];
	//$options = [ endpoint => $_ENV['NEON_PROJECT_ID'] ];

	try {
		$dbInfo = sprintf("pgsql:host=%s;port=%d;dbname=%s;sslmode=require", $host, $port, $dbname);
		$pdo = new PDO($dbInfo, $user, $password/*, $options*/);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		// Query
		$stmt = $pdo->query('SELECT name, description, author, date_created FROM markdown_files');
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	} catch (PDOException $e) {
		die("Database connection failed: " . $e->getMessage());
	}
?>

<!DOCTYPE HTML>
<HTML class="h-full w-full">
	<head>
		<meta charset="UTF-8">
		<title>Blue Docs</title>

		<link href="/public/output.css" rel="stylesheet" type="text/css">
		<script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>
	</head>

	<body class="flex flex-col justify-start h-full md:flex-row">
		<search class="flex flex-row w-full md:w-1/5 md:flex-col">
			<img src="/public/templogo.svg" alt="Temporary Blue Docs Logo" class="p-16 w-1/8 md:w-full">

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
			<form id="account" class="flex flex-col h-full">
				<div class="overflow-y-auto">
					<div>
						<h1>My Account</h1>

						<div class="flex flex-row px-2 py-6">
							<img class="size-12 mr-4" src="/public/profile.png" alt="Default Account Image">
							<div class="flex flex-col">
								Example Name
								<a>Edit display image</a>
							</div>
						</div>
					</div>

					<hr class="border-gray border-2">

					<div class="flex flex-row py-4">
						<div class="w-1/2 flex flex-col">
							<h1 class="text-xl font-bold">About</h1>
							<h2 class="text-md font-bold">First Name:</h2>
							<input id="firstname" type="text" name="firstname" autocomplete="firstname" placeholder="First Name" class="m-1 block w-1/2 float-left rounded-sm outline-2 px-1 bg-sky-200 outline-sky-400"/>
							<h2 class="text-md font-bold">Last Initial:</h2>
							<input id="lastinitial" type="text" name="lastinitial" autocomplete="lastinitial" maxlength="1" placeholder="Last Initial" class="m-1 block w-1/2 float-left rounded-sm outline-2 px-1 bg-sky-200 outline-sky-400"/>
							<h2 class="text-md font-bold">Email:</h2>
							<p>email@email.com</p>
							<h2 class="text-md font-bold">Role:</h2>
							<p>example role</p>
						</div>

						<div class="w-1/2 flex flex-col">
							<h1 class="text-xl font-bold">My Setup</h1>
							<h2 class="text-md font-bold">Default Language:</h2>
								<select class="m-1 block w-1/2 float-left rounded-sm outline-2 px-1 bg-sky-200 outline-sky-400">
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
								<select class="m-1 block w-1/2 float-left rounded-sm outline-2 px-1 bg-sky-200 outline-sky-400">
									<option>Select an OS</option>
									<option>Linux</option>
									<option>Mac OS</option>
									<option>Windows</option>
								</select>
							<h2 class="text-md font-bold">Default Game Engine:</h2>
								<select class="m-1 block w-1/2 float-left rounded-sm outline-2 px-1 bg-sky-200 outline-sky-400">
									<option>Select a game engine</option>
									<option>Godot</option>
									<option>Unity</option>
									<option>Unreal</option>
								</select>
						</div>
					</div>
				</div>

				<button type="submit" disabled class="w-1/4 mt-auto p-5 rounded-sm bg-sky-300 outline-sky-500 outline-2 hover:bg-sky-200 hover:outline-sky-400 focus:bg-sky-300 focus:outline-sky-500 disabled:bg-gray-300 disabled:outline-gray-500">
					Save Changes
				</button>
			</form>

			<div id="documents" hidden>
				<h1 class="text-xl">Documents</h1>

				<div>
					<h2 class="text-md font-bold">Recently Viewed</h2>

					<div class="relative">
						<div class="absolute pointer-events-none bg-linear-[to_right,white,transparent_0.75%,transparent_99.25%,white] h-full w-full"></div>
						<div class="overflow-x-auto grid grid-flow-col grid-rows-1 py-4">
							<?php
								foreach ($results as $document) {
									echo
									'<a href="#" class="w-sm flex flex-col gap-1 bg-sky-400 p-6 m-2 rounded-lg h-39">
										<h3 class="text-lg font-bold">' . $document['name'] . '</h3>
										<div>
											<p class="text-xs truncate">Author: <span class="authorName">' . $document['author'] . '</span></p>
											<p class="text-xs">Date Created: <span class="dateCreated">' . substr($document['date_created'], 0, strpos($document['date_created'], ' ')) . '<em hidden>' . substr($document['date_created'], strpos($document['date_created'], ' ')) . '</em></span></p>
										</div>
										<p class="text-sm line-clamp-2">' . $document['description'] . '</p>
									</a>';
								}
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
								foreach ($results as $document) {
									echo
									'<a href="#" class="w-sm flex flex-col gap-1 bg-sky-400 p-6 m-2 rounded-lg h-39">
										<h3 class="text-lg font-bold">' . $document['name'] . '</h3>
										<div>
											<p class="text-xs truncate">Author: <span class="authorName">' . $document['author'] . '</span></p>
											<p class="text-xs">Date Created: <span class="dateCreated">' . substr($document['date_created'], 0, strpos($document['date_created'], ' ')) . '<em hidden>' . substr($document['date_created'], strpos($document['date_created'], ' ')) . '</em></span></p>
										</div>
										<p class="text-sm line-clamp-2">' . $document['description'] . '</p>
									</a>';
								}
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
								foreach ($results as $document) {
									echo
									'<a href="#" class="w-sm flex flex-col gap-1 bg-sky-400 p-6 m-2 rounded-lg h-39">
										<h3 class="text-lg font-bold">' . $document['name'] . '</h3>
										<div>
											<p class="text-xs truncate">Author: <span class="authorName">' . $document['author'] . '</span></p>
											<p class="text-xs">Date Created: <span class="dateCreated">' . substr($document['date_created'], 0, strpos($document['date_created'], ' ')) . '<em hidden>' . substr($document['date_created'], strpos($document['date_created'], ' ')) . '</em></span></p>
										</div>
										<p class="text-sm line-clamp-2">' . $document['description'] . '</p>
									</a>';
								}
							?>
						</div>
					</div>
				</div>
			</div>

			<div id="notifications" hidden>
				Example Page
			</div>

			<div id="privacy" hidden>
				Example Page
			</div>

			<div id="feedback" hidden>
				Example Page
			</div>
		</main>
	</body>

	<script>
		const contentList = ['account', 'documents', 'notifications', 'privacy', 'feedback'];

		document.getElementById('account').addEventListener('submit', (event) => {
			/* Handle account change submissions */
		});

		function contentSelect(pageSelected) {
			for (const pageName of contentList) {
				if (pageSelected == pageName) {
					document.getElementById(pageName).hidden = false;
				} else {
					document.getElementById(pageName).hidden = true;
				}
			}
		}
	</script>
</HTML>