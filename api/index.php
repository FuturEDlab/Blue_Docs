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
<HTML>
	<head>
		<meta charset="UTF-8">
		<title>Blue Docs</title>

		<link href="/public/output.css" rel="stylesheet" type="text/css">
		<script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>
	</head>

	<body>

		<!-- Navbar Header -->
		<header class="sticky w-full h-[20vh] top-0 p-10 bg-white nav-header">

			<!-- Logo -->
			<img src="/public/templogo.svg" alt="Temporary Blue Docs Logo" class="float-left h-full">

			<!-- Account Dropdown -->
			<el-dropdown class="float-right">

				<!-- Account Picture -->
				<button class="cursor-pointer">
					<span class="sr-only">Open Profile Menu</span>
					<img class="size-8" src="/public/profile.png" alt="Default Account Image">
				</button>

				<!-- Dropdown Contents -->
				<el-menu anchor="bottom end" popover>
					<div class="py-1">
						<button onclick="window.location.href='/login'">Sign In to BlueDocs</button>
					</div>
				</el-menu>

			</el-dropdown>
		</header>

		<!-- Search Header -->
		<search class="sticky top-[20vh] flex flex-col bg-white justify-between gap-6 pb-6">

			<!-- Welcome Header Text -->
			<h1 class="text-4xl text-center">Welcome<?php
				if (isset($_COOKIE['user'])) {
					echo ', ' . $_COOKIE['user'];
				}
			?>!</h1>

			<div>
				<!-- Search Bar -->
				<div class="flex justify-center">
					<form class="w-3/4 md:w-3/8">
						<div class="relative">
							<input id="search" type="search" name="search" autocomplete="search" placeholder="Search Documents" required class="block w-full rounded-4xl outline-2 px-4 py-2"/>
							<button type="button" onclick="applyFilters()" class="cursor-pointer absolute end-0 bottom-0 text-white bg-sky-500 hover:bg-sky-400 border border-white border-4 font-medium leading-5 rounded-4xl text-xs px-3 py-1.5 focus:bg-sky-500">Search</button>
						</div>
					</form>
					<button command="show-modal" commandfor="filterDrawer" class="cursor-pointer ml-3">
						<span class="sr-only">Open Filter Menu</span>
						<img src="https://static.thenounproject.com/png/filter-icon-8291957-512.png" alt="Filter Icon" class="size-7">
					</button>
				</div>

				<!-- Sort Dropdown -->
				<el-dropdown class="flex justify-center">

					<!-- Dropdown Button -->
					<button class="cursor-pointer flex mt-2 text-lg font-bold items-center">
						Sort
						<svg viewBox="0 0 100 100" class="ml-2 h-4">
							<polygon points="50 15, 100 100, 0 100"/>
						</svg>
					</button>

					<!-- Dropdown Contents -->
					<el-menu id="sortMenu" anchor="bottom" popover>
						<div id="sortDropdown" class="py-1 flex flex-row gap-3">
							<label for="alphabeticalForward" class="select-none relative cursor-pointer text-sm rounded-xl p-2 has-checked:bg-sky-300 bg-gray-300">
								<input checked type="radio" onclick="sort(this)" id="alphabeticalForward" name="sort" class="cursor-pointer absolute appearance-none inset-0" required/>
								A → Z
							</label>

							<label for="alphabeticalBackward" class="select-none relative cursor-pointer text-sm rounded-xl p-2 has-checked:bg-sky-300 bg-gray-300 justify-center items-center">
								<input type="radio" onclick="sort(this)" id="alphabeticalBackward" name="sort" class="cursor-pointer absolute appearance-none inset-0" required/>
								Z → A
							</label>

							<label for="chronologicalForward" class="select-none relative cursor-pointer text-sm rounded-xl p-2 has-checked:bg-sky-300 bg-gray-300 justify-center items-center">
								<input type="radio" onclick="sort(this)" id="chronologicalForward" name="sort" class="cursor-pointer absolute appearance-none inset-0" required/>
								New → Old
							</label>

							<label for="chronologicalBackward" class="select-none relative cursor-pointer text-sm rounded-xl p-2 has-checked:bg-sky-300 bg-gray-300 justify-center items-center">
								<input type="radio" onclick="sort(this)" id="chronologicalBackward" name="sort" class="cursor-pointer absolute appearance-none inset-0" required/>
								Old → New
							</label>
						</div>
					</el-menu>
				</el-dropdown>
			</div>

			<!-- Filter Drawer -->
			<el-dialog>
				<dialog id="filterDrawer" class="fixed inset-0 size-auto max-h-none max-w-none bg-transparent backdrop:bg-transparent">
					<el-dialog-backdrop class="fixed inset-0 bg-gray-900/50 transition-opacity data-closed:opacity-0 data-enter:duration-300 data-enter:ease-out data-leave:duration-200 data-leave:ease-in"></el-dialog-backdrop>

					<div tabindex="0" class="absolute inset-y-0 left-0 pr-10 focus:outline-none">
						<el-dialog-panel class="group/dialog-panel flex flex-col mr-auto block size-full max-w-md bg-gray-300 rounded rounded-4 rounded-tl-[0px] rounded-bl-[0px] border border-gray-600 border-3 border-l-0 transform transition duration-500 ease-in-out data-closed:-translate-x-full sm:duration-700 p-5">

							<!-- Filter Header -->
							<div class="flex mt-0">
								<h2 class="text-3xl font-bold">Filter</h2>
								<button type="button" command="close" commandfor="filterDrawer" class="ml-auto font-bold hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500">
									<span class="sr-only">Close Filter Panel</span>
									X
								</button>
							</div>

							<!-- Filter Options -->
							<div class="my-auto overflow-auto flex flex-col">

							</div>

							<!-- Filter Application Buttons -->
							<div>
								<button type="button" class="text-sm m-3 p-3 rounded-sm bg-gray-300 outline-gray-500 hover:bg-gray-200 hover:outline-gray-400 focus:bg-gray-300 focus:outline-gray-500 outline-3">Clear Filter</button>
								<button type="button" class="text-sm m-3 p-3 rounded-sm bg-sky-200 outline-sky-400 hover:bg-sky-100 hover:outline-sky-300 focus:bg-sky-200 focus:outline-sky-400 outline-3">Apply Changes</button>
							</div>
						</el-dialog-panel>
					</div>
				</dialog>
			</el-dialog>
		</search>

		<!-- Main Content -->
		<main class="-z-1 overflow-y-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 p-6 pt-0">
			<?php
				foreach ($results as $document) {
					echo
					'<a href="#" hidden class="flex flex-col gap-1 bg-sky-400 p-6 m-6 rounded-lg h-39">
						<h3 class="text-lg font-bold">' . $document['name'] . '</h5>
						<div>
							<p class="text-xs truncate">Author: ' . $document['author'] . '</p>
							<p class="text-xs">Date Created: <span>' . substr($document['date_created'], 0, strpos($document['date_created'], ' ')) . '<em hidden>' . substr($document['date_created'], strpos($document['date_created'], ' ')) . '</em></span></p>
						</div>
						<p class="text-sm line-clamp-2">' . $document['description'] . '</p>
					</a>';
				}
				foreach ($results as $document) {
					echo
					'<a href="#" hidden class="flex flex-col gap-1 bg-sky-400 p-6 m-6 rounded-lg h-39">
						<h3 class="text-lg font-bold">' . $document['name'] . '</h5>
						<div>
							<p class="text-xs truncate">Author: ' . $document['author'] . '</p>
							<p class="text-xs">Date Created: <span>' . substr($document['date_created'], 0, strpos($document['date_created'], ' ')) . '<em hidden>' . substr($document['date_created'], strpos($document['date_created'], ' ')) . '</em></span></p>
						</div>
						<p class="text-sm line-clamp-2">' . $document['description'] . '</p>
					</a>';
				}
				foreach ($results as $document) {
					echo
					'<a href="#" hidden class="flex flex-col gap-1 bg-sky-400 p-6 m-6 rounded-lg h-39">
						<h3 class="text-lg font-bold">' . $document['name'] . '</h5>
						<div>
							<p class="text-xs truncate">Author: ' . $document['author'] . '</p>
							<p class="text-xs">Date Created: <span>' . substr($document['date_created'], 0, strpos($document['date_created'], ' ')) . '<em hidden>' . substr($document['date_created'], strpos($document['date_created'], ' ')) . '</em></span></p>
						</div>
						<p class="text-sm line-clamp-2">' . $document['description'] . '</p>
					</a>';
				}
				foreach ($results as $document) {
					echo
					'<a href="#" hidden class="flex flex-col gap-1 bg-sky-400 p-6 m-6 rounded-lg h-39">
						<h3 class="text-lg font-bold">' . $document['name'] . '</h5>
						<div>
							<p class="text-xs truncate">Author: ' . $document['author'] . '</p>
							<p class="text-xs">Date Created: <span>' . substr($document['date_created'], 0, strpos($document['date_created'], ' ')) . '<em hidden>' . substr($document['date_created'], strpos($document['date_created'], ' ')) . '</em></span></p>
						</div>
						<p class="text-sm line-clamp-2">' . $document['description'] . '</p>
					</a>';
				}
			?>
		</main>

		<div id="docsMessage" class="text-center"></div>
	</body>

	<script>
		/* Prevent sort dropdown from closing on click. */
		document.getElementById('alphabeticalForward').addEventListener('click', function(event) {
			event.stopImmediatePropagation();
		});
		document.getElementById('alphabeticalBackward').addEventListener('click', function(event) {
			event.stopImmediatePropagation();
		});
		document.getElementById('chronologicalForward').addEventListener('click', function(event) {
			event.stopImmediatePropagation();
		});
		document.getElementById('chronologicalBackward').addEventListener('click', function(event) {
			event.stopImmediatePropagation();
		});

		function applyFilters() {
			const main = document.querySelector('main');
			const searchValue = document.getElementById('search').value;
			let docsEmpty = true;

			for (const child of main.children) {
				if (child.querySelector('h3').innerHTML.toLocaleLowerCase().indexOf(searchValue.toLocaleLowerCase()) > -1) {
					child.hidden = false;
					docsEmpty = false;
				} else {
					child.hidden = true;
				}
			}

			if (docsEmpty) {
				document.getElementById('docsMessage').textContent = 'No documents found.';
			} else {
				document.getElementById('docsMessage').textContent = '';
			}
		}

		function sort(radioButton) {
			const main = document.querySelector('main');
			let docs = Array.from(main.children);

			if (radioButton.id == 'alphabeticalForward') {
				docs.sort((a, b) => a.querySelector('h3').innerHTML.localeCompare(b.querySelector('h3').innerHTML));
			} else if (radioButton.id == 'alphabeticalBackward') {
				docs.sort((a, b) => b.querySelector('h3').innerHTML.localeCompare(a.querySelector('h3').innerHTML));
			} else if (radioButton.id == 'chronologicalForward') {
				docs.sort((a, b) => new Date(b.querySelector('span').innerText + b.querySelector('em').innerText).getTime()
					- new Date(a.querySelector('span').innerText + a.querySelector('em').innerText).getTime());
			} else if (radioButton.id == 'chronologicalBackward') {
				docs.sort((a, b) => new Date(a.querySelector('span').innerText + a.querySelector('em').innerText).getTime()
					- new Date(b.querySelector('span').innerText + b.querySelector('em').innerText).getTime());
			}

			main.innerHTML = '';
			for (let i = 0; i < docs.length; i++) {
				main.appendChild(docs[i]);
			}
		}

		document.addEventListener('DOMContentLoaded', function() {
			sort(document.getElementById('alphabeticalForward'));
			applyFilters();
		});
	</script>
</HTML>