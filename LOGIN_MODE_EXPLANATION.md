# Signup and login mode explanation

The working page is [`login.php`](login.php). It uses one PHP page for both signup and login. The `mode` value decides which behavior and form fields to show. The commented snippets below explain the lines that make that work.

## Choose the mode

```php
// Start a session so PHP can remember the signed-in user across requests.
session_start();

// If the session already has a user ID, send the signed-in user to the home page.
if (!empty($_SESSION['user_id'])) {
    // Tell the browser to request the home page next.
    header('Location: index.php');
    // Stop this request so the login page is not rendered after the redirect.
    exit;
}

// Read mode from the URL first, then from the submitted form, then default to login.
// Accept only the exact value "signup"; every other value becomes "login".
$mode = ($_GET['mode'] ?? $_POST['mode'] ?? 'login') === 'signup' ? 'signup' : 'login';
```

The URL link `login.php?mode=signup` opens signup mode. When the form is submitted, the hidden input below sends the current mode in the POST request, so the server keeps processing the correct action.

## Keep the selected mode when submitting

```php
// Begin a form that submits its fields to login.php using an HTTP POST request.
<form method="post" action="login.php">
    <!-- Store the current mode in the submitted form without showing an editable field. -->
    <input type="hidden" name="mode" value="<?= htmlspecialchars($mode, ENT_QUOTES, 'UTF-8') ?>">
```

`htmlspecialchars` escapes special characters before the value is placed in HTML. The mode is restricted to `signup` or `login` above, and escaping is still a good output-safety practice.

## Run the matching server-side action

```php
// If this request submitted the form, read the values the visitor entered.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Remove whitespace around the submitted email address.
    $email = trim($_POST['email'] ?? '');
    // Remove whitespace around the submitted username, if present.
    $username = trim($_POST['username'] ?? '');
    // Read the password as entered; passwords are not trimmed or displayed back.
    $password = $_POST['password'] ?? '';

    // Signup mode validates and creates a new account.
    if ($mode === 'signup') {
        // ...validate fields, check for an existing account, hash the password, and insert the user.
    // Otherwise, login mode checks the email and password against an existing account.
    } else {
        // ...look up the account and verify its stored password hash.
    }
}
```

The `...` lines here summarize the longer validation and database operations in `login.php`; they are explanatory placeholders, not PHP statements to copy into the working page.

## Show the right page text and fields

```php
// Pick a browser page title based on the selected mode.
$title = $mode === 'signup' ? 'Create account' : 'Log in';

// In the HTML, show the name field only while creating an account.
<?php if ($mode === 'signup'): ?>
    <!-- This input collects the new account's name. -->
    <label for="username">Name</label>
    <input id="username" name="username" type="text" required>
<?php endif; ?>

<!-- Set the browser's password autofill hint according to the current action. -->
<input type="password" autocomplete="<?= $mode === 'signup' ? 'new-password' : 'current-password' ?>" required>

<!-- Change the button label so it describes the action that will happen. -->
<button type="submit"><?= $mode === 'signup' ? 'Create account' : 'Log in' ?></button>
```

The same conditional pattern is used for the heading, introduction, and link between signup and login. This keeps one shared page while showing the fields and wording that fit the chosen mode.

## Switch between signup and login

```php
<!-- In login mode, add ?mode=signup to the URL; in signup mode, link back to plain login.php. -->
<a href="login.php<?= $mode === 'signup' ? '' : '?mode=signup' ?>">
    <!-- Label the link with the destination action. -->
    <?= $mode === 'signup' ? 'Log in' : 'Create an account' ?>
</a>
```

The mode is normalized on the server, preserved in the form, and then used both for account processing and for choosing what the page displays. That is the workaround that lets one page handle both flows.
