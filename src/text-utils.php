<?php

$ansi_colors = [
  '{Reset}' => '\[\033[0m\]',
  '{Black}' => '\[\033[0;30m\]',
  '{DarkBlue}' => '\[\033[0;34m\]',
  '{DarkGreen}' => '\[\033[0;32m\]',
  '{DarkCyan}' => '\[\033[0;36m\]',
  '{DarkRed}' => '\[\033[0;31m\]',
  '{DarkMagenta}' => '\[\033[0;35m\]',
  '{DarkYellow}' => '\[\033[0;33m\]',
  '{Gray}' => '\[\033[0;37m\]',
  '{DarkGray}' => '\[\033[1;30m\]',
  '{Blue}' => '\[\033[1;34m\]',
  '{Green}' => '\[\033[1;32m\]',
  '{Cyan}' => '\[\033[1;36m\]',
  '{Red}' => '\[\033[1;31m\]',
  '{Magenta}' => '\[\033[1;35m\]',
  '{Yellow}' => '\[\033[1;33m\]',
  '{White}' => '\[\033[1;37m\]',
];

$ansi_background_colors = [
  '{Black}' => '\[\033[40m\]',
  '{Blue}' => '\[\033[44m\]',
  '{Green}' => '\[\033[42m\]',
  '{Cyan}' => '\[\033[46m\]',
  '{Red}' => '\[\033[41m\]',
  '{Magenta}' => '\[\033[45m\]',
  '{Yellow}' => '\[\033[43m\]',
  '{Gray}' => '\[\033[47m\]',
];

/*
$ansi_colors = [
  'Black' => 30,
  'DarkBlue' => 34,
  'DarkGreen' => 32,
  'DarkCyan' => 36,
  'DarkRed' => 31,
  'DarkMagenta' => 35,
  'DarkYellow' => 33,
  'Gray' => 37,
  'DarkGray' => 90,
  'Blue' => 94,
  'Green' => 92,
  'Cyan' => 36,//96,
  'Red' => 91,
  'Magenta' => 95,
  'Yellow' => 93,
  'White' => 97,
];*/

function colorTextWithEmbeddedColors($text) {
  global $ansi_colors;
  return strtr($text, $ansi_colors);
}

function colorText($text, $color) {
  $fg = color($color);
  $reset = color('');
  //$bg = background(''); //@todo support bg colors

  return $fg . $text . $reset;
}

function color($color, $background = false) {
  global $ansi_colors;
  global $ansi_background_colors;
  
  if ($color == '') $color = '{Reset}';
  else $color = '{' . $color . '}';

  $code = $background ? $ansi_background_colors[$color] : $ansi_colors[$color];
  return $code;
}

function startsWith($haystack, $needle) {
  $length = strlen($needle);
  return (substr($haystack, 0, $length) === $needle);
}

function endsWith($haystack, $needle) {
  $length = strlen($needle);

  return $length === 0 ||
    (substr($haystack, -$length) === $needle);
}