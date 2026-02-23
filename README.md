# Blue Docs

A documentation website to store Markdown docs for use by teams on GVSU's Innovation and Research department.

## Getting Started

### Development

To develop for Blue Docs, clone the repository using `git clone https://github.com/FuturEDlab/Blue_Docs.git`. If you have access, this will allow you to pull and push changes to the remote repository.

To develop you will need to install [PHP](https://www.php.net/) or a tool with the PHP built-in webserver (like [XAMPP](https://www.apachefriends.org/download.html)) and the [Tailwind CLI](https://github.com/tailwindlabs/tailwindcss/releases).

Before developing webpages, create an input.css file in the public folder and add the line `@import "tailwindcss";`. Then, while developing, run the command `tailwindcss -i public/input.css -o public/output.css --watch` from the project's root directory (substitute "tailwindcss" for the path to the executable if needed). This will allow Tailwind to edit output.css as needed. Make sure to add output.css to your commits to see the styling changes in deployments.

To view a page locally, run the command `php -S localhost:<PORT>` (with a port of your choosing) and go to the given URL in a browser to view the page. Unfortunately, the Vercel CLI is dependent on the "punycode" module, which is not compatible with the `vercel dev` command and PHP, so we cannot properly view the project locally without editing URL paths for the PHP server or deploying to a branch in the GitHub repository.

### Deployment

To create a Vercel deployment, simply push changes to the repository with `git add <file_path>`, `git commit -m "<message>"`, and `git push`. Go to the project's Vercel page and open the "Deployments" tab to find your changes. If pushing to the main branch, the deployment with be under "Production," otherwise it will be under "Preview."
