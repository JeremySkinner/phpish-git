<?php

$ansi_default = 39;

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
];

$ansi_esc = chr(27) . '[';

function foreground($Color) {
  return color($Color);
}

function background($Color) {
  return color($Color, 10);
}

function color($color, $offset = 0) {
  global $ansi_colors;
  global $ansi_default;
  global $ansi_esc;

  //    $r = ($color >> 16) & 0xff;
//    $g = ($color >> 8) & 0xff;
//    $b = $color & 0xff;
//
//    return $esc . (38 + $offset) . ";2;$r;$g;{$b}m";

  if ($color !== '') {
    $code = $ansi_colors[$color] + $offset;

    if ($color == 'Cyan') {
      print "$code\n";
    }

    return "{$ansi_esc}{$code}m";
  }

  $code = $ansi_default + $offset;
  return "{$ansi_esc}{$code}m";
}