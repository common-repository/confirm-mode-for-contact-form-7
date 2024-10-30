<?php
if ( empty( $user_options ) ) return;
?>
<style>
    #contact-form-editor .form-table.cm4cf7-admin-editor-panel th {
        width: 120px;
    }
    .cm4cf7-admin-editor-panel [type="checkbox"]:not(:checked) + span {
        color: #aaa;
    }
</style>

<h2><?php _e( 'Confirm Mode', 'confirm-mode-for-contact-form-7' ); ?></h2>

<table class="form-table cm4cf7-admin-editor-panel">
    <tr>
        <th><?php _e( 'Confirm Mode', 'confirm-mode-for-contact-form-7' ); ?></th>
        <td>
            <p>
                <label>
                    <input type="checkbox"
                           name="cm4cf7_user_options[USE_CONFIRM_MODE]"
                           <?php if ( ! empty( $user_options['USE_CONFIRM_MODE'] ) ): ?>
                           checked
                           <?php endif; ?>
                    >
                    <span><?php _e( 'Enable', 'confirm-mode-for-contact-form-7' ); ?></span>
                </label>
            </p>
        </td>
    </tr>
    <tr>
        <th><?php _e( 'Confirm Button', 'confirm-mode-for-contact-form-7' ); ?></th>
        <td>
            <input type="text"
                   name="cm4cf7_user_options[CONFIRM_BUTTON_TEXT]"
                   value="<?php echo esc_attr( $user_options['CONFIRM_BUTTON_TEXT'] ); ?>"
            >
        </td>
    </tr>
    <tr>
        <th><?php _e( 'Return Button', 'confirm-mode-for-contact-form-7' ); ?></th>
        <td>
            <input type="text"
                   name="cm4cf7_user_options[RETURN_BUTTON_TEXT]"
                   value="<?php echo esc_attr( $user_options['RETURN_BUTTON_TEXT'] ); ?>"
            >
        </td>
    </tr>
    <tr>
        <th><?php _e( 'Confirm Message', 'confirm-mode-for-contact-form-7' ); ?></th>
        <td>
            <textarea name="cm4cf7_user_options[CONFIRM_MESSAGE]"
                      class="large-text"
            ><?php echo esc_html( $user_options['CONFIRM_MESSAGE'] ); ?></textarea>
        </td>
    </tr>
    <tr>
        <th><?php _e( 'Auto Scroll', 'confirm-mode-for-contact-form-7' ); ?></th>
        <td>
            <p>
                <label>
                    <input type="checkbox"
                           name="cm4cf7_user_options[AUTO_SCROLL]"
                           <?php if ( ! empty( $user_options['AUTO_SCROLL'] ) ): ?>
                           checked
                           <?php endif; ?>
                    >
                    <span><?php _e( 'Enable', 'confirm-mode-for-contact-form-7' ); ?></span>
                </label>
            </p>
        </td>
    </tr>
</table>
