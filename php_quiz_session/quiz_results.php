<?
// See page 1 for explanation of these two lines
ini_set('session.gc_maxlifetime', 60 * 2);
session_set_cookie_params(60 * 2);
session_start();

// See page 1 for explanation of these two lines
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

if (!isset($_SESSION['name']) || !isset($_SESSION['ans_q1']) || !isset($_SESSION['signature'])) {
    header('Location: quiz_page1.php?expired=1');
    exit;
}

$ans_q1 = $_SESSION['ans_q1'];
$ans_q2 = $_SESSION['ans_q2'];
$ans_q3 = $_SESSION['ans_q3'];
$name = $_SESSION['name'];
$signature = $_SESSION['signature'];

// Correct answers — provided for you
$correct_q1 = 'cat';
$correct_q2 = 'true';   // whales ARE mammals
$correct_q3 = 'eagle';  // eagle is NOT a mammal

// Grade — provided for you
$score = 0;
$score += ($ans_q1 == $correct_q1) ? 1 : 0;
$score += ($ans_q2 == $correct_q2) ? 1 : 0;
$score += ($ans_q3 == $correct_q3) ? 1 : 0;

$completed_ts = time();
$cookie_expires_timestamp = $completed_ts + 60 * 60 * 24 * 365;
setcookie('quiz_last_completed', $completed_ts, $cookie_expires_timestamp);

?>
<!DOCTYPE html>
<html>

<head>
    <title>Quiz — Results</title>
</head>

<body>

    Dear, <?= htmlspecialchars($signature) ?>,
    <br><br>
    Thank you for completing the quiz, <?= htmlspecialchars($name) ?>.
    <br>

    Date Completed: <?= date('F j, Y \a\t g:ia', $completed_ts) ?>
    <br><br>

    <b>Your Results:</b>
    <br><br>

    <table border="1" cellpadding="5" cellspacing="0">
        <tr>
            <td><b>Question</b></td>
            <td><b>Your Answer</b></td>
            <td><b>Correct Answer</b></td>
            <td><b>Result</b></td>
        </tr>
        <tr>
            <td>Q1: Which is a mammal?</td>
            <td><?= htmlspecialchars($ans_q1) ?></td>
            <td><?= $correct_q1 ?></td>
            <td><?= ($ans_q1 == $correct_q1) ? '✓ Correct' : '✗ Wrong'; ?></td>
        </tr>
        <tr>
            <td>Q2: Whales are mammals (T/F)</td>
            <td><?= htmlspecialchars($ans_q2) ?></td>
            <td><?= $correct_q2 ?></td>
            <td><?= ($ans_q2 == $correct_q2) ? '✓ Correct' : '✗ Wrong'; ?></td>
        </tr>
        <tr>
            <td>Q3: Which is NOT a mammal?</td>
            <td><?= htmlspecialchars($ans_q3) ?></td>
            <td><?= $correct_q3 ?></td>
            <td><?= ($ans_q3 == $correct_q3) ? '✓ Correct' : '✗ Wrong'; ?></td>
        </tr>
    </table>

    <br>
    <b>Score: <?= $score ?> out of 3</b>
    <br><br>

    Should we find that you have cheated on this quiz, rest assured that our lawyers will be in touch.
    <br><br>
    <a href="quiz_page1.php?restart=1">Take the Quiz Again</a>

</body>

</html>