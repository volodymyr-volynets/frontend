<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Numbers\Frontend\HTML\Renderers\Common\Interface2;

interface Base
{
    // basic elements
    public static function a(array $options = []): string;
    public static function img(array $options = []): string;
    public static function script(array $options = []): string;
    public static function style(array $options = []): string;
    public static function table(array $options = []): string;
    public static function grid(array $options): string;
    public static function fieldset(array $options = []): string;
    public static function ul(array $options = []): string;
    public static function mandatory(array $options = []): string;
    public static function tooltip(array $options = []): string;
    public static function message(array $options = []): string;
    public static function element(array $options = []): string;
    // simple tags
    public static function tag(array $options = []): string;
    public static function div(array $options = []): string;
    public static function span(array $options = []): string;
    public static function label(array $options = []): string;
    public static function br(array $options = []): string;
    public static function hr(array $options = []): string;
    // form related elements
    public static function form(array $options = []): string;
    public static function input(array $options = []): string;
    public static function inputGroup(array $options = []): string;
    public static function radio(array $options = []): string;
    public static function checkbox(array $options = []): string;
    public static function password(array $options = []): string;
    public static function file(array $options = []): string;
    public static function hidden(array $options = []): string;
    public static function textarea(array $options = []): string;
    public static function select(array $options = []): string;
    public static function multiselect(array $options = []): string;
    public static function icon(array $options = []): string;
    // assemblies
    public static function calendar(array $options = []): string;
    public static function separator(array $options = []): string;
    public static function pills(array $options = []): string;
    public static function segment(array $options = []): string;
    public static function menu(array $options = []): string;
    public static function tabs(array $options = []): string;
    //public static function captcha($options = []);
    //public static function captcha_validate($captcha_id, $password);
    // form buttons
    public static function button(array $options = []): string;
    public static function button2(array $options = []): string;
    public static function submit(array $options = []): string;
    // special handling function for options
    public static function renderValueFromOptions($value, array $options): string;
}
