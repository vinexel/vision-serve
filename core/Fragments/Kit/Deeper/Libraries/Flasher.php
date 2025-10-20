<?php

/**
 * Vinexel Framework.
 *
 * @package Vision
 * @author Elwira Perdana
 * @copyright (c) PT Iconic Wira Niaga
 * @license MIT License
 */

namespace Deeper\Libraries;

/**
 * Flasher Class
 *
 * The `Flasher` library provides a simple and framework-agnostic way
 * to display flash messages in your application.
 * 
 * It supports both **Bootstrap** and **Tailwind CSS** styling systems,
 * and allows for fully custom CSS classes as well.
 *
 * Flash messages are stored temporarily in session and automatically
 * cleared after being displayed once.
 */
class Flasher
{
    /**
     * Set a flash message into the session.
     *
     * @param string $message     The main message to display.
     * @param string $action      Optional secondary text or call-to-action.
     * @param string $type        Type of message (success|danger|warning|info).
     * @param string $framework   Frontend framework to use (bootstrap|tailwind).
     * @param string $customClass Additional custom CSS classes.
     * 
     * @return void
     */
    public static function setFlash($message, $action = '', $type = 'info', $framework = 'bootstrap', $customClass = '')
    {
        Session::set('flash', [
            'message'     => $message,
            'action'      => $action,
            'type'        => $type,
            'framework'   => $framework,  // 'bootstrap' or 'tailwind'
            'customClass' => $customClass,
            'time'        => time()
        ]);
    }

    /**
     * Display the flash message stored in session.
     *
     * @param bool $return Whether to return the HTML instead of printing it.
     * 
     * @return string|void HTML string if $return is true, otherwise prints directly.
     */
    public static function flash($return = false)
    {
        $flash = Session::get('flash');
        if (!$flash) return;

        $html = '';

        if ($flash['framework'] === 'tailwind') {
            $colorMap = [
                'success' => 'green',
                'danger'  => 'red',
                'warning' => 'yellow',
                'info'    => 'blue',
            ];
            $color = $colorMap[$flash['type']] ?? 'blue';

            $html = '<div id="flasher" class="transition-opacity duration-500 ease-in-out opacity-100 bg-' . $color . '-100 border border-' . $color . '-400 text-' . $color . '-700 px-4 py-3 rounded relative ' . $flash['customClass'] . '" role="alert">
                        <strong class="font-bold">' . $flash['message'] . '</strong>
                        <span class="block sm:inline">' . $flash['action'] . '</span>
                        <span onclick="this.parentElement.remove()" class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer">
                            &times;
                        </span>
                    </div>';
        } else {
            // default: Bootstrap
            $html = '<div id="flasher" class="alert alert-' . $flash['type'] . ' alert-dismissible fade show ' . $flash['customClass'] . '" role="alert">
                        <strong>' . $flash['message'] . '</strong> ' . $flash['action'] . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
        }

        // Auto-dismiss 6 seconds (JS inject)
        $html .= '<style>#flasher {
    transition: opacity 0.5s ease;}</style>
    <script>
            setTimeout(function() {
                const el = document.getElementById("flasher");
                if (el) {
                    el.classList.add("opacity-0");
                    setTimeout(() => el.remove(), 500);
                }
            }, 6000);
        </script>';

        Session::remove('flash');

        return $return ? $html : print($html);
    }

    /**
     * Check whether a flash message is empty or not.
     *
     * @return bool True if no flash message exists, false otherwise.
     */
    public static function isEmpty()
    {
        return empty(Session::get('flash'));
    }
}
