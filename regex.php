<?php

// input and output files
$input_file  = "test.html";
$output_file = "output.txt";

$raw_html = file_get_contents($input_file);
$cleaned_html = preg_replace('/\s+/', ' ', $raw_html);

// function to pull expressions based on regex patterns,
// I use html_entity_decode and strip_tags to clean up the results
// and trim to remove whitespace. I use this function with
// various combinations of html tags and pure regex patterns
function pull_expression($pattern, $raw_html)
{
  // preg_match returns 1 expression match
  if (preg_match($pattern, $raw_html, $matches)) {
    // strip the html
    return trim(html_entity_decode(strip_tags($matches[1])));
  } else {
    return "items not found";
  }
}

// pull name
$name = pull_expression('/<h2 class="name">(.*?)<\/h2>/i', $cleaned_html);

// pull title and dept
$title_dept = pull_expression('/<li class="text-wrap">(.*?)<\/li>/i', $cleaned_html);

// set empties for when not found
$title = "title not found";
$department = "dept not found";

// split title and department if both exist
if (strpos($title_dept, '-') !== false) {
  list($title, $department) = array_map('trim', explode('-', $title_dept, 2));
} else {
  $title = $title_dept;
}

// pull office
$office = pull_expression('/class="location">(.*?)<\/a>/i', $cleaned_html);


// pull phone
// tried to do this without using class and pure regex patterns but it was super difficult
if (preg_match('/class="phone">[^>]*?(\+?\d[\d\s\-]{9,14})/i', $cleaned_html, $match)) {
  $phonenum = trim($match[1]);
} else {
  $phonenum = "phone # not found";
}

// pull email
if (preg_match('/[\w\.-]+@txstate\.edu/i', $cleaned_html, $match)) {
  $email = $match[0];
} else {
  $email = "email not found";
}

// pull bio
$bio = pull_expression('/<div class="bio-content">.*?<!-- HTML_TAG_START -->(.*?)<!-- HTML_TAG_END -->/is', $cleaned_html);
// wordwrap the bio to 80 characters per line so it doesn't fill up terminal
$bio = wordwrap($bio, 80, "\n");

// output the expressions
$output = "Name: $name\n" .
  "Title: $title\n" .
  "Department: $department\n" .
  "Office: $office\n" .
  "Phone: $phonenum\n" .
  "Email: $email\n" .
  "Biography and education: $bio\n";

// write the regex to output.txt
file_put_contents($output_file, $output);

echo "> $output_file\n";
