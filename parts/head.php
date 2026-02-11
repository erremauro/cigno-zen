<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo cignozen_get_title(); ?></title>
    <link rel="icon" type="image/png" href="/assets/images/favicon/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/assets/images/favicon/favicon.svg" />
    <link rel="shortcut icon" href="/assets/images/favicon/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/images/favicon/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Cigno Zen" />
    <link rel="manifest" href="/assets/images/favicon/site.webmanifest" />
    <?php wp_head(); ?>

    <script>
    (function () {
      try {
        var root = document.documentElement;
        var userTheme = <?php echo wp_json_encode( cignozen_get_user_theme_mode() ); ?>;
        var OVERRIDE_KEY = 'cz-theme-override';

        function scheduledTheme() {
          var h = new Date().getHours();
          return (h >= 7 && h < 18) ? 'light' : 'dark';
        }

        function readOverride() {
          try {
            var raw = localStorage.getItem(OVERRIDE_KEY);
            if (!raw) return null;
            var obj = JSON.parse(raw);
            if (!obj || (obj.exp || 0) < Date.now() || (obj.theme !== 'light' && obj.theme !== 'dark')) {
              localStorage.removeItem(OVERRIDE_KEY);
              return null;
            }
            return obj.theme;
          } catch (err) {
            try { localStorage.removeItem(OVERRIDE_KEY); } catch (_) {}
            return null;
          }
        }

        root.setAttribute('data-user-theme', userTheme);

        if (userTheme === 'dark' || userTheme === 'light') {
          root.setAttribute('data-theme', userTheme);
          return;
        }

        root.setAttribute('data-theme', readOverride() || scheduledTheme());
      } catch (e) {}
    })();
    </script>
</head>
