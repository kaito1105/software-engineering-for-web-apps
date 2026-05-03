<?
// See page 1 for explanation of these two lines
ini_set('session.gc_maxlifetime', 60 * 2);
session_set_cookie_params(60 * 2);
session_start();

// See page 1 for explanation of these two lines
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

if (!isset($_SESSION['name']) || !isset($_SESSION['ans_q1'])) {
    header('Location: quiz_page1.php?expired=1');
    exit;
}

if (isset($_SESSION['signature'])) {
    header('Location: quiz_results.php');
    exit;
}

$task = $_POST['task'] ?? '';

$ans_q2 = $_POST['ans_q2'] ?? '';
$ans_q3 = $_POST['ans_q3'] ?? '';
$signature = trim($_POST['signature'] ?? '');

if ($task == 'submit') {
    if ($ans_q2 == '') {
        $error = 'Please answer Question 2.';
    } else if ($ans_q3 == '') {
        $error = 'Please answer Question 3.';
    } else if ($signature == '') {
        $error = 'Please sign before submitting.';
    }

    if (!$error) {
        $_SESSION['ans_q2'] = $ans_q2;
        $_SESSION['ans_q3'] = $ans_q3;
        $_SESSION['signature'] = $signature;
        header('Location: quiz_results.php');
        exit;
    }
}

$name = $_SESSION['name'];

?>
<!DOCTYPE html>
<html>

<head>
    <title>Quiz for <?= htmlspecialchars($name) ?> — Page 2</title>
</head>

<body>
    <h3>Quiz for <?= htmlspecialchars($name) ?> — Page 2</h3>

    <? if ($error) { ?>
        <span style="color:red;"><?= $error ?></span>
        <br><br>
    <? } ?>

    <b>Question 1 answer locked in.</b> &nbsp; <a href="quiz_page1.php?edit=1">Go back to edit</a>
    <br><br>

    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
        <input type="hidden" name="task" value="submit">

        <b>Question 2:</b> True or False — Whales are mammals.
        <br><br>
        <input type="radio" name="ans_q2" value="true" <?= ($ans_q2 == 'true') ? 'checked' : '' ?>> True
        <br>
        <input type="radio" name="ans_q2" value="false" <?= ($ans_q2 == 'false') ? 'checked' : '' ?>> False
        <br><br>

        <b>Question 3:</b> Which of the following is <b>not</b> a mammal?
        <br><br>
        <select name="ans_q3">
            <option value="">-- Select --</option>
            <option value="dog" <?= ($ans_q3 == 'dog') ? 'selected' : '' ?>>Dog</option>
            <option value="eagle" <?= ($ans_q3 == 'eagle') ? 'selected' : '' ?>>Eagle</option>
            <option value="dolphin" <?= ($ans_q3 == 'dolphin') ? 'selected' : '' ?>>Dolphin</option>
            <option value="horse" <?= ($ans_q3 == 'horse') ? 'selected' : '' ?>>Horse</option>
        </select>
        <br><br>

        Verify that you have not cheated on this quiz by signing below.
        <br>
        <input type="text" name="signature" value="<?= htmlspecialchars($signature) ?>" placeholder="Sign here">
        <br><br>

        <button type="submit">Submit Quiz</button>
    </form>

</body>

</html>