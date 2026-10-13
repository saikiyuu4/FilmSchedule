<?php

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
function formatDate(string $date): string
{
    return date('d/m/Y', strtotime($date));
}
function formatPrice(float|int|string $price): string
{
    return number_format((float)$price, 0, ',', '.') . 'đ';
}
?>