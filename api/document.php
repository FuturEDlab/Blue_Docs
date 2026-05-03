<?php
	if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        /* Pull document information via SQL connection. */

	    /* Set SQL Settings */
	    $host = $_ENV['BLUE_DOCS_PGHOST'];
	    $port = $_ENV['BLUE_DOCS_PGPORT'] ?? 5432;
	    $dbname = $_ENV['BLUE_DOCS_PGDATABASE'];
	    $user = $_ENV['BLUE_DOCS_PGUSER'];
	    $password = "endpoint=" . $_ENV['BLUE_DOCS_NEON_PROJECT_ID'] . ";" . $_ENV['BLUE_DOCS_PGPASSWORD'];
	    //$options = [ endpoint => $_ENV['NEON_PROJECT_ID'] ];

        /* Fetch file for display. */
        try {
            /* Set Connection Details */
            $dbInfo = sprintf("pgsql:host=%s;port=%d;dbname=%s;sslmode=require", $host, $port, $dbname);
            $pdo = new PDO($dbInfo, $user, $password/*, $options*/);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            /* Set SQL Query */
            $stmt = $pdo->query('SELECT * FROM markdown_files WHERE id = ' . $_GET['id'] . ' LIMIT 1');
            $docResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }

        /* Fetch comments related to file. */
        try {
            /* Set SQL Query */
            $stmt = $pdo->query('SELECT * FROM comments WHERE doc_id = ' . $_GET['id']);
            $commentResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }

        /* Fetch user names and images and append to comment array. */
        try {
            foreach ($commentResults as &$comment) {
                $stmt = $pdo->query('SELECT name, image FROM neon_auth.user WHERE id = \'' . $comment['user_id'] . '\' LIMIT 1');
                $userResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $comment['name'] = $userResults[0]['name'];
                $comment['image'] = $userResults[0]['image'];
            }
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }

	/* Return if not a GET Request */
	} else {
		http_response_code(405);
		exit('Method not allowed.');
	}
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title><?php echo $docResults[0]['name'] ?></title>

        <link href="/output.css" rel="stylesheet" type="text/css">
        <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>
    </head>

    <body>
        <main class="flex justify-center p-4">
            <!-- Document Page -->
	        <div id="docContainer" shadowrootmode="open" class="markdown-body flex flex-col w-7/10 p-4 rounded-xl">
                <link href="https://cdnjs.cloudflare.com/ajax/libs/github-markdown-css/5.8.1/github-markdown.min.css" rel="stylesheet" type="text/css">

                <?php
                    echo $docResults[0]['name'] . '.md'; 
                ?>

                <?php
                    require '../lib/Parsedown.php';

                    $parsedown = new Parsedown();
                    $parsedown -> setSafeMode(false); //Turning this on escapes < and > and displays "<br>"
                    $htmlOutput = $parsedown->text(stream_get_contents($docResults[0]['contents']));

                    echo $htmlOutput;
                ?>
            </div>

            <!-- Comment Drawer -->
			<el-dialog>
				<dialog id="commentDrawer" onClick="determineSlide(event)" class="fixed inset-0 size-auto max-h-none max-w-none bg-transparent backdrop:bg-transparent">
					<div id="clickBox" tabindex="0" class="absolute inset-y-0 right-0 w-3/10 focus:outline-none">
						<el-dialog-panel class="group/dialog-panel mr-auto block size-full p-4 pl-3 transform transition duration-500 ease-in-out data-closed:translate-x-full sm:duration-700">
                            <div class="size-full bg-gray-300 rounded-xl p-3 overflow-auto flex flex-col gap-4">
                                <h2>Comments</h2>

                                <?php
                                    foreach ($commentResults as $comment) {
                                        $date = new DateTime();
					                    $date->setTimestamp(strtotime($comment['date_uploaded']));
					                    $date->setTimezone(new DateTimeZone('America/Detroit'));
					                    $date = $date->format(DateTime::W3C);

                                        echo
                                        '<div class="bg-gray-400 rounded-lg flex flex-col gap-2 p-2">
                                            <div>
                                                <h3>' . $comment['image'] . ' ' . $comment['name'] . '</h3>
                                                <h3>' . substr($date, 0, strpos($date, 'T')) . '</h3>
                                            </div>
                                            <p>' . $comment['contents'] . '</p>
                                        </div>';
                                    }
                                ?>
                            </div>
						</el-dialog-panel>
					</div>
				</dialog>
			</el-dialog>
        </main>

        <button command="show-modal" commandfor="commentDrawer" onclick="docSlide()" class="fixed cursor-pointer top-5 right-5">
			<span class="sr-only">Open Filter Menu</span>
			<img src="https://static.thenounproject.com/png/filter-icon-8291957-512.png" alt="Filter Icon" class="size-8">
		</button>
    </body>

    <script>
        var slideToggle = true;

        /* Slides document when comment drawer is opened. */
        function docSlide() {
            if (slideToggle) {
                document.getElementById('docContainer').animate([
                    { transform: 'translateX(0)' },
                    { transform: 'translateX(-21.429%)' }
                ], {
                    duration: 500,
                    easing: 'ease-in-out',
                    fill: 'forwards'
                });
            } else {
                document.getElementById('docContainer').animate([
                    { transform: 'translateX(-21.429%)' },
                    { transform: 'translateX(0)' }
                ], {
                    duration: 500,
                    easing: 'ease-in-out',
                    fill: 'forwards'
                });
            }
            slideToggle = !slideToggle;
        }

        /* Slides document back if comment drawer begins to close. */
        function determineSlide(event) {
            if (!document.getElementById('clickBox').contains(event.target)) {
                docSlide();
            }
        }
    </script>
</html>
