<?
// $_GET and $_POST are built-in PHP SuperGlobals that handle those kind of form 
// submissions, respectively.
//The line below merges both into one array so that this script will handle both 
// GET and POST form submissions.

$submitted_form_data = array_merge($_GET, $_POST);
?>
<!DOCTYPE html>
<html>

<head>
  <title>Echo of HTML Form Submission</title>
</head>

<body>
  This script echos back the data in all <tt>name=value</tt> pairs submitted
  by any HTML form.
  <hr>
  <br>

  <table width="" cellspacing="0" cellpadding="3">
    <tr>
      <th>Submitted Name</th>
      <td width="15">&nbsp;</td>
      <th>Submitted Value</th>
    </tr>

    <? foreach ($submitted_form_data as $key => $value) { ?>
      <tr>
        <td><?= $key ?></td>
        <td>&nbsp;</td>
        <td><?= $value ?></td>
      </tr>
    <? } ?>

  </table>
  <h5>
    1. What does the echo script return for a checkbox if it is not checked when
    the form is submitted? Explain why in terms of the query string.
  </h5>
  <p>
    If a check box is not checked, the echo script returns nothing for that checkbox.
    Submitted name and submitted value do not appear in the table. The is because when
    a form is submitted, only checked checkboxes are included in the query string for
    GET or the request body for POST requests. If a checkbox is not selected, it is not
    included in the submitted data at all.
  </p>

  <h5>
    2. When multiple items in your multiple select menu are chosen when the form is
    submitted, what does the echo script return? Why?
  </h5>
  <p>
    If the multiple select menu is written as <code>name="ingredients[]"</code>, PHP treats
    the submitted values as an array. When multiple items are selected, the echo script
    receives an array. Since the script directly prints <code>$value</code>, PHP displays
    the word "Array" instead of listing the individual elements. However, if the multiple
    select menu is written without brackets, such as <code>name="ingredients"</code>, PHP
    does not treat the values as an array. Instead, each selected value overwrites the
    previous one, and only the last selected item is stored and displayed.
  </p>

  <h5>
    3. Why does it not make sense to put <code>required</code> in your checkboxes? Contrast
    that with how <code>required</code> works with the radio buttons.
  </h5>
  <p>
    It does not make sense to pup <code>required</code> on individual checkboxes because
    checkboxes allow users to select zero, one, or multiple options independently. Adding
    <code>required</code> to each checkbox would force all of them to be selected. In
    contrast, radio buttons are designed to allow users to select exactly one option from a
    mutually exclusive group. When <code>required</code> is used with radio buttons (sharing
    the same name attribute), it ensures that the user selects one option from the group
    before submitting the form.
  </p>

  <h5>
    4. How can you disable HTML validation (perhaps temporarily for testing) of a form
    without removing all of the <code>required</code> attributes.
  </h5>
  <p>
    We can disable HTML validation by adding <code>novalidate</code> attribute to the
    <<code>form</code>> tag, like this: <br>
    <<code>form name="form" action="echo.php" method="POST" novalidate</code>><br>
    This disables the browser's built-in validation while keeping the <code>required</code>
    attributes in the code.
  </p>

  <h5>5. Briefly explain the difference between <code>method="GET"</code> and
    <code>method="POST"</code> in terms of the browser’s behavior when you hit the reload
    button on the page returned AFTER a form is submitted.
  </h5>
  <p>
    When using <code>method="GET"</code>, the submitted data appears in the URL as a query
    string. If you hit the reload button, the browser simply reloads the same URL without
    showing a warning message. On the other hand, when using <code>method="POST"</code>, the
    submitted data is sent in the request body and does not appear in the URL. If you reload
    the page, the browser displays a warning message such as “Confirm Form Resubmission”
    because reloading would resend the POST data to the server.
  </p>

</body>

</html>