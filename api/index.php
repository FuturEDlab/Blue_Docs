<!DOCTYPE HTML>
<HTML>
	<head>
		<meta charset="UTF-8">
		<title>Blue Docs</title>

		<link href="/styles/output.css" rel="stylesheet" type="text/css">
		<script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>
	</head>

	<body>

		<!-- Navbar Header -->
		<header class="sticky w-full h-[20vh] top-0 p-10 nav-header">

			<!-- Logo -->
			<img src="/public/templogo.svg" alt="Temporary Blue Docs Logo" class="float-left h-full">

			<!-- Account Dropdown -->
			<el-dropdown class="float-right">

				<!-- Account Picture -->
				<button>
					<span class="sr-only">Open Profile Menu</span>
					<img class="size-8" src="/public/profile.png" alt="Default Account Image">
				</button>

				<!-- Dropdown Contents -->
				<el-menu anchor="bottom end" popover>
					<div class="py-1">
						<button onclick="window.location.href='login.php';">Sign In to BlueDocs</button>
					</div>
				</el-menu>

			</el-dropdown>
		</header>

		<!-- Main Content -->
		<main class="relative min-h-screen p-6">
			<p class="font-bold">Content</p>
		</main>
	</body>

	<!-- Dummy script to prevent rendering before loading CSS -->
	<script>0</script>
</HTML>
