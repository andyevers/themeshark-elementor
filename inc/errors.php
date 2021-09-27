<?php

namespace Themeshark_Elementor\Inc;

if (!defined('ABSPATH')) exit;

final class TS_Error
{
    public static function die($message, $backtrace_limit = 1, $backtrace_args = DEBUG_BACKTRACE_IGNORE_ARGS)
    {
        $backtrace_limit++; //always skip this function
        $traces = debug_backtrace($backtrace_args, $backtrace_limit);

        for ($i = 0; $i < sizeof($traces); $i++) { //starts after this function
            $trace = $traces[$i];
            $file = basename($trace['file']);
            $line = $trace['line'];
            $function = $trace['function'];

            $trace_message = '';
            if ($i === 0) {
                $trace_message = "TS_Error thrown in: <strong>$file</strong> - Line: <strong>$line</strong>";
                echo wp_kses($trace_message, ['strong' => []]);
                continue;
            }
            if ($i === 1) {
                $trace_message .= '<br><br>Callstack<br>';
            }

            $allowed_html = [
                'strong' => [],
                'br' => []
            ];
            $trace_message .= "File: <strong>$file</strong> - Line: <strong>$line</strong> - Function <strong>$function</strong><br>";
            echo wp_kses($trace_message, $allowed_html);
        }
        wp_die("<div style='font-size: 17px'>Message</div><div style='font-size: 17px;'>$message</div>");
    }
}
