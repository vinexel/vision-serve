<?php

namespace Deeper\Libraries;

class Flasher
{
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

    public static function isEmpty()
    {
        return empty(Session::get('flash'));
    }
}
