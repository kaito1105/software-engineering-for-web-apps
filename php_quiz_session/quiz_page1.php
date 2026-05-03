<?
// Override the default 24-minute session timeout — server destroys session data after this
ini_set('session.gc_maxlifetime', 60 * 2); // 2 minutes
session_set_cookie_params(60 * 2); // Sets the session cookie lifetime in the browser to match
session_start();

// Tell the browser never to cache this page.
// Without this, the back button can show a stale cached copy, bypassing session guards.
// no-store  = don't save the page at all
// no-cache  = always revalidate with the server before using any cached copy
// must-revalidate = don't serve stale content even if server is unreachable
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache'); // older HTTP/1.0 equivalent — included for backward compatibility

$task = $_POST['task'] ?? '';

$name = trim($_POST['name'] ?? '');
$ans_q1 = $_POST['ans_q1'] ?? '';

if (isset($_GET['restart']) && $_GET['restart'] == 1) {
    session_unset();
    session_destroy();
    session_start();
}

// Option 1: Strict session control (fully prevents returning to Page 1)
// This approach blocks backward navigation completely by always redirecting forward
// if session data exists. This strictly follows the requirement but does not allow
// users to revisit Page 1 once they proceed.
// if (isset($_SESSION['ans_q1'])) {
//     if (isset($_SESSION['signature'])) {
//         header('Location: quiz_results.php');
//     } else {
//         header('Location: quiz_page2.php');
//     }
//     exit;
// }

// Option 2: Instructor-style approach (allows controlled return to Page 1)
// This approach allows returning to Page 1 using a query parameter (?edit=1).
// However, this introduces a potential loophole since the URL can be manually modified.
// The implementation below follows this approach while still relying on session checks.
$edit = $_GET['edit'] ?? '';
if (isset($_SESSION['ans_q1']) && $edit != 1) {
    if (isset($_SESSION['signature'])) {
        header('Location: quiz_results.php');
    } else {
        header('Location: quiz_page2.php');
    }
    exit;
}

if ($task == 'submit') {
    if ($name == '') {
        $error = 'Please enter your name.';
    } else if ($ans_q1 == '') {
        $error = 'Please answer Question 1.';
    }

    if (!$error) {
        $_SESSION['name'] = $name;
        $_SESSION['ans_q1'] = $ans_q1;
        $cookie_expires_timestamp = time() + 60 * 60 * 24 * 365;

        setcookie('quiz_name', $name, $cookie_expires_timestamp);
        header('Location: quiz_page2.php');
        exit;
    }
}

if (isset($_COOKIE['quiz_name']) && $_COOKIE['quiz_name'] != '') {
    $name = $_COOKIE['quiz_name'];
    $welcome_back = "Welcome back, " . htmlspecialchars($name) . "!";
}

if (isset($_SESSION['ans_q1'])) {
    $ans_q1 = $_SESSION['ans_q1'];
}

if (isset($_COOKIE['quiz_last_completed'])) {
    $last_completed = $_COOKIE['quiz_last_completed'];
}

if (isset($_POST['name'])) {
    $name = trim($_POST['name'] ?? '');
}

?>
<!DOCTYPE html>
<html>

<head>
    <title>Quiz — Page 1</title>
</head>

<body>
    <h3>Complete the Quiz</h3>

    <? if ($welcome_back) { ?>
        <b><?= $welcome_back ?></b>
        <br>
        <? if ($last_completed) { ?>
            Last quiz completed: <?= date('F j, Y \a\t g:ia', $last_completed) ?>
        <? } ?>
        <br><br>
    <? } ?>

    <? if ($error) { ?>
        <span style="color:red;"><?= $error ?></span>
        <br><br>
    <? } ?>

    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
        <input type="hidden" name="task" value="submit">

        Your Name: <input type="text" name="name" value="<?= htmlspecialchars($name) ?>">
        <br><br>

        <b>Question 1:</b> Which of the following is a mammal?
        <br><br>
        <input type="radio" name="ans_q1" value="cat" <?= ($ans_q1 == 'cat') ? 'checked' : '' ?>> Cat
        <br>
        <input type="radio" name="ans_q1" value="parrot" <?= ($ans_q1 == 'parrot') ? 'checked' : '' ?>> Parrot
        <br>
        <input type="radio" name="ans_q1" value="salmon" <?= ($ans_q1 == 'salmon') ? 'checked' : '' ?>> Salmon
        <br>
        <input type="radio" name="ans_q1" value="iguana" <?= ($ans_q1 == 'iguana') ? 'checked' : '' ?>> Iguana
        <br><br>
        <button type="submit">Continue</button>
    </form>

</body>

</html>