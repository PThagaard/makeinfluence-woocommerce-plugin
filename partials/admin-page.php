<div class="wrap">
  <form method="post" action="<?php echo esc_html(admin_url('options.php')); ?>">
    <?php settings_fields('makeinfluence-settings-group'); ?>

    <table class="form-table" role="presentation">
      <tbody>
        <tr>
          <td colspan="2">
            <p><img src="<?php echo esc_url( plugins_url( '', __FILE__ ) ); ?>/MIlogo.png"></p>
            <a href="https://app.makeinfluence.com/" target="_BLANK" style="color:#00bfcd"><?php _e('Make Influence Dashboard', 'makeinfluence'); ?></a> || <a href="https://app.makeinfluence.com/business-signup" target="_BLANK" style="color:#00bfcd"><?php _e('Opret gratis konto', 'makeinfluence'); ?></a>
          </td>
        </tr>

        <tr>
          <th scope="row">
            <label for="business_id">
              <?php esc_html_e('Business ID', 'makeinfluence'); ?>
            </label>
          </th>
          <td>
            <input name="makeinfluence_business_id" type="text" id="business_id" value="<?php echo esc_attr(get_option('makeinfluence_business_id')); ?>" class="regular-text code">
          </td>
        </tr>

        <tr>
          <th scope="row">
            <label for="ios_tracking">
              <?php esc_html_e('Deaktiver iOS Tracking', 'makeinfluence'); ?>
            </label>
          </th>
          <td>
            <input type="checkbox" name="makeinfluence_ios_tracking" id="ios_tracking" value="1" <?php checked(1, get_option('makeinfluence_ios_tracking'), true); ?> />
            <?php esc_html_e('Deaktiver', 'makeinfluence'); ?>
            <br><i><?php esc_html_e('Tjek denne boks, hvis du selv har indsat iOS page- og conversion tracking.', 'makeinfluence'); ?></i>
          </td>
        </tr>

        <tr>
          <th scope="row">
            <label for="debug_log">
              <?php esc_html_e('Aktiver debug log', 'makeinfluence'); ?>
            </label>
          </th>
          <td>
            <input type="checkbox" name="makeinfluence_debug_log" id="debug_log" value="1" <?php checked(1, get_option('makeinfluence_debug_log'), true); ?> />
            <?php esc_html_e('Aktiver debug', 'makeinfluence'); ?>
            <?php
            if (get_option('makeinfluence_debug_log')) {
                $url = admin_url('admin.php?page=wc-status&tab=logs');
                echo '(<a href="' . esc_url($url) . '" target="_blank">' . __('Se log', 'makeinfluence') . '</a>)';
            }
            ?>
            <br><i><?php esc_html_e('Debugging bør ikke benyttes på LIVE hjemmesider over længere tid.', 'makeinfluence'); ?></i>
          </td>
        </tr>

        <?php if (get_option('makeinfluence_debug_log')) { ?>

          <tr style="border-top: 1px solid #002d35;">
            <th scope="row" style="padding:0;padding-top: 20px;">
              Woocommerce
            </th>
            <td style="padding:0;padding-top: 20px;">
              <?php if (class_exists('WooCommerce')) { ?>
                <?php echo "<span style='color:green;font-weight:bold;'>" . __('Aktiveret', 'makeinfluence') . "</span>"; ?>
                <?php if (!empty(WC_VERSION)) { ?>
                  (Version <?php echo WC_VERSION; ?>)
                <?php } ?>
              <?php } else { ?>
                <?php echo "<span style='color:red;font-weight:bold;'>" . __('Deaktiveret', 'makeinfluence') . "</span>"; ?>
              <?php } ?>
            </td>
          </tr>

          <tr>
            <th scope="row" style="padding:0;">
              Woocommerce Subscriptions
            </th>
            <td style="padding:0;">
              <?php if (class_exists('WC_Subscriptions')) { ?>
                <?php echo "<span style='color:green;font-weight:bold;'>" . __('Aktiveret', 'makeinfluence') . "</span>"; ?>
                <?php if (!empty(WC_Subscriptions::$version)) { ?>
                  (Version <?php echo WC_Subscriptions::$version; ?>)
                <?php } ?>
              <?php } else { ?>
                <?php echo "<span style='color:red;font-weight:bold;'>" . __('Deaktiveret', 'makeinfluence') . "</span>"; ?>
              <?php } ?>
            </td>
          </tr>

          <?php if (class_exists('WooCommerce')) { ?>
            <tr>
              <th colspan="2" style="font-weight: normal;">
                <?php
                esc_html_e('Din WooCommerce "ordre-modtaget" side er sat til:', 'makeinfluence');
                echo " <strong>" . esc_attr(wc_get_endpoint_url('order-received', '', wc_get_page_permalink('checkout'))) . "{order_id}/?key={key}</strong>. ";
                esc_html_e('Har du problemer med Make Influence tracking, så doubletjek at kunderne lander på denne side efter gennemført køb.', 'makeinfluence');
                ?>
              </th>
            </tr>
          <?php } ?>

        <?php } ?>
      </tbody>
    </table>

    <?php submit_button(); ?>
  </form>
</div>
