<?php
	/* Pass requests to update user account information. */
	if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_COOKIE['__Secure-neon-auth_session_token'])) {
		$postData = json_decode(file_get_contents('php://input'), true);

		/* Edit user's attributes via Neon data API. */
		if (isset($postData['user_id']) && (isset($postData['name']) || isset($postData['image']))) {
			/* Set Auth URL */
			$url = $_ENV['BLUE_DOCS_NEON_AUTH_BASE_URL'] . '/update-user';

			/* Construct Headers */
			$headers = [
				'Content-Type: application/json',
				'Accept: application/json',
				'Origin: ' . $_ENV['VERCEL_URL']
			];

			/* Construct JSON Data */
			$data = [];
			if (isset($postData['name'])) { $data['name'] = htmlspecialchars($postData['name']); }
			if (isset($postData['image'])) { $data['image'] = htmlspecialchars($postData['image']); }

			/* Initialize cURL */
			$ch = curl_init();

			/* Set cURL Options */
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
			curl_setopt($ch, CURLOPT_COOKIE, '__Secure-neon-auth.session_token=' . $_COOKIE['__Secure-neon-auth_session_token']);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			/* Handle cURL Response and End Session */
			$response = curl_exec($ch);
			if ($response === false) {
				die('cURL Error: ' . curl_error($ch));
			} else {
				echo $response;
			}
		}

		/* Edit user's settings via SQL connection. */
		if (isset($postData['user_id']) && (isset($postData['default_language']) || isset($postData['default_os']) || isset($postData['default_engine']))) {
			/* Set SQL Settings */
			$host = $_ENV['PGHOST'];
			$port = $_ENV['PGPORT'] ?? 5432;
			$dbname = $_ENV['PGDATABASE'];
			$user = $_ENV['PGUSER'];
			$password = "endpoint=" . $_ENV['NEON_PROJECT_ID'] . ";" . $_ENV['PGPASSWORD'];
			//$options = [ endpoint => $_ENV['NEON_PROJECT_ID'] ];

			try {
				/* Set Connection Details */
				$dbInfo = sprintf("pgsql:host=%s;port=%d;dbname=%s;sslmode=require", $host, $port, $dbname);
				$pdo = new PDO($dbInfo, $user, $password/*, $options*/);
				$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

				/* Create List of Edited Settings */
				$settings = [];
				if (isset($postData['default_language'])) { $settings['default_language'] = htmlspecialchars($postData['default_language']); }
				if (isset($postData['default_os'])) { $settings['default_os'] = htmlspecialchars($postData['default_os']); }
				if (isset($postData['default_engine'])) { $settings['default_engine'] = htmlspecialchars($postData['default_engine']); }
				$settingsKeys = array_keys($settings);

				/* Set SQL Query */
				if (count($settings) == 1) {
					$stmt = $pdo->query('UPDATE user_settings SET ' . $settingsKeys[0] . ' = \'' . $settings[$settingsKeys[0]] . '\' WHERE user_id = \'' . htmlspecialchars($postData['user_id']) . '\'');
				} else if (count($settings) > 1) {
					$query = 'UPDATE user_settings SET ';
					foreach (array_slice($settingsKeys, 0, -1) as $key) {
						$query .= $key . ' = \'' . $settings[$key] . '\', ';
					}
					$stmt = $pdo->query($query . end($settingsKeys) . ' = \'' . $settings[end($settingsKeys)] . '\' WHERE user_id = \'' . htmlspecialchars($postData['user_id']) . '\'');
				}
			} catch (PDOException $e) {
				die("Database connection failed: " . $e->getMessage());
			}
		}

		exit();

	/* Return if not a POST Request */
	} else {
		http_response_code(405);
		exit('Method not allowed.');
	}
?>
