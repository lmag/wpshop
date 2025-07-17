/**
 * Use this file for JavaScript code that you want to run in the front-end 
 * on posts/pages that contain this block.
 *
 * When this file is defined as the value of the `viewScript` property
 * in `block.json` it will be enqueued on the front end of the site.
 *
 * Example:
 *
 * ```js
 * {
 *   "viewScript": "file:./view.js"
 * }
 * ```
 *
 * If you're not making any changes to this file because your project doesn't need any 
 * JavaScript running in the front-end, then you should delete this file and remove 
 * the `viewScript` property from `block.json`. 
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#view-script
 */
 
import { render } from '../utils';
import { __ } from '@wordpress/i18n';
import { useEffect, useState } from '@wordpress/element';

import { useSelect, useDispatch } from '@wordpress/data';

const AuthStatus = (props) => {
    const { user_email, user_avatar, my_account_link } = props;
    const isConnected = user_email && user_email.trim() !== '';

    // Avatar par défaut simple
    const defaultAvatar = 'https://www.gravatar.com/avatar/?d=mp&s=24';

    // State pour gérer l'erreur de chargement d'image
    const [avatarError, setAvatarError] = useState(false);

    const handleClick = () => {
        if (my_account_link) {
            window.location.href = my_account_link;
        }
    };

    const handleImageError = () => {
        setAvatarError(true);
    };

    // Déterminer quelle image afficher
    const getAvatarSrc = () => {
        if (avatarError || !isConnected || !user_avatar) {
            return defaultAvatar;
        }
        return user_avatar;
    };

    return (
        <div className="wp-block-wpshop-auth-status-block">
            <div className="auth-status-header" onClick={handleClick}>
                <img
                    src={getAvatarSrc()}
                    alt="Avatar"
                    className="auth-status-avatar"
                    onError={handleImageError}
                />
                <span className="auth-status-text">
                    {isConnected ? user_email : __('Mon compte', 'auth-status-block')}
                </span>
            </div>
        </div>
    );
}

render(<AuthStatus
    user_email={document.getElementsByClassName('wp-block-wpshop-auth-status-block')[0].getAttribute('data-user-email')}
    user_avatar={document.getElementsByClassName('wp-block-wpshop-auth-status-block')[0].getAttribute('data-user-avatar')}
    my_account_link={document.getElementsByClassName('wp-block-wpshop-auth-status-block')[0].getAttribute('data-my-account-link')}
/>, '.wp-block-wpshop-auth-status-block');
export default AuthStatus;