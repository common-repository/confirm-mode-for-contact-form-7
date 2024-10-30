
addEventListener( 'DOMContentLoaded', () => {

    document.querySelectorAll( '.wpcf7' ).forEach( wpcf7 => {

        // Take over the form settings from server side
        function UserOptions_Load() {
            const elm = wpcf7.querySelector( '[name="_cm4cf7_user_options"]' );
            const json = elm?.value;
            elm.remove();
            return JSON.parse( json ) || {};
        }

        const UserOptions = UserOptions_Load();

        if ( ! UserOptions.USE_CONFIRM_MODE ) return;

        const forms = wpcf7.querySelectorAll( '.wpcf7-form' );
        const submits = wpcf7.querySelectorAll( '.wpcf7-submit' );

        // Submit button text replacer
        function SubmitLabel_Init() {
            submits.forEach( submit => {
                submit.setAttribute( 'data-cm4cf7-default-value', submit.value );
            } );
        }
        function SubmitLabel_Confirm() {
            submits.forEach( submit => {
                submit.value = UserOptions.CONFIRM_BUTTON_TEXT;
                submit.dispatchEvent( new CustomEvent( 'cm4cf7_submit_value_replaced', { bubbles: true } ) );
            } );
        }
        function SubmitLabel_Send() {
            submits.forEach( submit => {
                submit.value = submit.getAttribute( 'data-cm4cf7-default-value' );
                submit.dispatchEvent( new CustomEvent( 'cm4cf7_submit_value_restored', { bubbles: true } ) );
            } );
        }

        // Request type confirm/send
        function RequestType_Confirm() {
            forms.forEach( form => {
                form.querySelectorAll( '[name^="_cm4cf7_request_"]').forEach( elm => elm.remove() );
                const input = document.createElement( 'input' );
                input.type = 'hidden';
                input.name = '_cm4cf7_request_confirm';
                input.value = 'true';
                form.appendChild( input );
            } );
        }
        function RequestType_Send() {
            forms.forEach( form => {
                form.querySelectorAll( '[name^="_cm4cf7_request_"]').forEach( elm => elm.remove() );
                const input = document.createElement( 'input' );
                input.type = 'hidden';
                input.name = '_cm4cf7_request_send';
                input.value = 'true';
                form.appendChild( input );
            } );
        }

        // Return button create/destroy
        function ReturnButton_Create() {
            const button = document.createElement( 'button' );
            button.type = 'button';
            button.textContent = UserOptions.RETURN_BUTTON_TEXT;
            button.classList.add( 'cm4cf7-return-button' );
            button.addEventListener( 'click', onReturnClick );
            if ( submits.length ) {
                submits[0].parentNode.insertBefore( button, submits[0] );
                // event ignition
                const option = { bubbles: true };
                option.detail = {
                    newNode: button,
                    referenceNode: submits[0]
                };
                submits[0].dispatchEvent( new CustomEvent( 'cm4cf7_return_button_inserted', option ) );
            }
        }
        function ReturnButton_Destroy() {
            wpcf7.querySelectorAll( '.cm4cf7-return-button' ).forEach( elm => elm.remove() );
        }

        // Confirmation view create/destroy
        function ConfirmView_Create() {
            // Insert <span> to display values for each form component
            function text( elm, className ) {
                const span = document.createElement( 'span' );
                span.classList.add( 'cm4cf7-confirm-value' );
                span.classList.add( className );
                span.textContent = ( elm.type === 'password' ) ? elm.value.replace( /./g, '*' ) : elm.value;
                elm.parentNode.insertBefore( span, elm );
                fire( span, elm );
            }
            function textarea( elm, className ) {
                const span = document.createElement( 'span' );
                span.classList.add( 'cm4cf7-confirm-value' );
                span.classList.add( className );
                span.textContent = elm.value;
                span.innerHTML = span.innerHTML.replaceAll( '\n', '<br>' );
                elm.parentNode.insertBefore( span, elm );
                fire( span, elm );
            }
            function select( elm, className ) {
                const options = Array.prototype.filter.call( elm, option => option.selected );
                const values = options.map( option => option.value );
                const span = document.createElement( 'span' );
                span.classList.add( 'cm4cf7-confirm-value' );
                span.classList.add( className );
                span.textContent = values.join( '\n' );
                span.innerHTML = span.innerHTML.replaceAll( '\n', '<br>' );
                elm.parentNode.insertBefore( span, elm );
                fire( span, elm );
            }
            function checkbox( elm, className ) {
                const checkboxes = elm.querySelectorAll( '[type="checkbox"]:checked' );
                const values = Array.prototype.map.call( checkboxes, checkbox => checkbox.value );
                const span = document.createElement( 'span' );
                span.classList.add( 'cm4cf7-confirm-value' );
                span.classList.add( className );
                span.textContent = values.join( ', ' );
                elm.parentNode.insertBefore( span, elm );
                fire( span, elm );
            }
            function radio( elm, className ) {
                const radios = elm.querySelectorAll( '[type="radio"]:checked' );
                const values = Array.prototype.map.call( radios, radio => radio.value );
                const span = document.createElement( 'span' );
                span.classList.add( 'cm4cf7-confirm-value' );
                span.classList.add( className );
                span.textContent = values.join( ', ' );
                elm.parentNode.insertBefore( span, elm );
                fire( span, elm );
            }
            function quiz( elm, className ) {
                // TODO: Difficult to support "quiz"
                //   - The answer field on the confirmation screen will be updated to blank.
                //   - If there are multiple questions, the question change on the confirmation screen.
            }
            // event ignition
            function fire( span, elm ) {
                const option = { bubbles: true };
                option.detail = {
                    newNode: span,
                    referenceNode: elm
                };
                elm.dispatchEvent( new CustomEvent( 'cm4cf7_confirm_value_inserted', option ) );
            }
            wpcf7.classList.add( 'cm4cf7-confirm-view' );
            wpcf7.querySelectorAll( '.wpcf7-text' ).forEach( elm => text( elm, 'cm4cf7-text' ) );
            wpcf7.querySelectorAll( '.wpcf7-date' ).forEach( elm => text( elm, 'cm4cf7-date' ) );
            wpcf7.querySelectorAll( '.wpcf7-number' ).forEach( elm => text( elm, 'cm4cf7-number' ) );
            wpcf7.querySelectorAll( '.wpcf7-select' ).forEach( elm => select( elm, 'cm4cf7-select' ) );
            wpcf7.querySelectorAll( '.wpcf7-textarea' ).forEach( elm => textarea( elm, 'cm4cf7-textarea' ) );
            wpcf7.querySelectorAll( '.wpcf7-checkbox' ).forEach( elm => checkbox( elm, 'cm4cf7-checkbox' ) );
            wpcf7.querySelectorAll( '.wpcf7-radio' ).forEach( elm => radio( elm, 'cm4cf7-radio' ) );
            wpcf7.querySelectorAll( '.wpcf7-range' ).forEach( elm => text( elm, 'cm4cf7-range' ) );
            wpcf7.querySelectorAll( '.wpcf7-quiz' ).forEach( elm => quiz( elm, 'cm4cf7-quiz' ) );
            wpcf7.querySelectorAll( '.wpcf7-file' ).forEach( elm => text( elm, 'cm4cf7-file' ) );
            // Insert a message at the top of the form to prompt for confirmation
            const message = document.createElement( 'div' );
            message.classList.add( 'cm4cf7-message-for-confirmation' );
            message.textContent = UserOptions.CONFIRM_MESSAGE;
            message.innerHTML = message.innerHTML.replaceAll( '\n', '<br>' );
            wpcf7.parentNode.insertBefore( message, wpcf7 );
            // auto scroll
            if ( UserOptions.AUTO_SCROLL ) {
                message.scrollIntoView( { behavior:'smooth', block:'start', inline:'nearest' } );
            }
        }
        function ConfirmView_Destroy() {
            const prev = wpcf7.previousSibling;
            if (prev?.classList?.contains('cm4cf7-message-for-confirmation')) prev.remove();
            wpcf7.querySelectorAll('.cm4cf7-confirm-value').forEach(elm => elm.remove());
            wpcf7.classList.remove('cm4cf7-confirm-view');
        }

        // Automatic check the Acceptance checkbox
        // (When CF7 has Ajax disabled, the Acceptance checkbox is unchecked in the HTML after POST)
        function AutoAccept() {
            const checkboxes = wpcf7.querySelectorAll( '.wpcf7-acceptance:not(.optional) [type="checkbox"]' );
            checkboxes.forEach( elm => elm.checked = true );
        }

        // When ready to send
        function onSendReady() {
            RequestType_Send();
            ConfirmView_Create();
            SubmitLabel_Send();
            ReturnButton_Create();
            AutoAccept();
            // event ignition
            wpcf7.dispatchEvent( new CustomEvent( 'cm4cf7_moved_to_confirm', { bubbles: true } ) );
        }

        // When return button clicked
        function onReturnClick() {
            RequestType_Confirm();
            ConfirmView_Destroy();
            SubmitLabel_Confirm();
            ReturnButton_Destroy();
            // hide response message from the server
            wpcf7.querySelectorAll( '.wpcf7-response-output' ).forEach( elm => elm.classList.remove( 'cm4cf7-show' ) );
            // auto scroll
            if ( UserOptions.AUTO_SCROLL ) {
                wpcf7.scrollIntoView( { behavior:'smooth', block:'start', inline:'nearest' } );
            }
            // event ignition
            wpcf7.dispatchEvent( new CustomEvent( 'cm4cf7_returned_to_input', { bubbles: true } ) );
        }

        // After mail sent
        function onMailSent() {
            RequestType_Confirm();
            ConfirmView_Destroy();
            SubmitLabel_Confirm();
            ReturnButton_Destroy();
        }

        // CF7 result message (.wpcf7-response-output) filter
        function ResponseOutputFilter( elm ) {
            // Once hidden, then
            elm.classList.remove( 'cm4cf7-show' );
            // For messages that the plugin has stopped sending
            if ( 'CM4CF7_MAILSEND_ABORTED' === elm.textContent.trim() ) {
                // Entering confirmation mode with message hidden.
                onSendReady();
            } else {
                // Other messages are displayed as they were.
                elm.classList.add( 'cm4cf7-show' );
            }
        }

        // CF7 result messages (.wpcf7-response-output) observer
        function ResponseOutputObserver() {
            const observer = new MutationObserver( mutations => {
                mutations.forEach( mutation => {
                    if ( mutation.target.classList?.contains( 'wpcf7-response-output' ) ) {
                        ResponseOutputFilter( mutation.target );
                    }
                } );
            } );
            const observeOptions = {
                childList: true,
                subtree: true
            };
            observer.observe( wpcf7, observeOptions );
        }

        wpcf7.classList.add( 'cm4cf7' );
        wpcf7.querySelectorAll( '.wpcf7-response-output' ).forEach( ResponseOutputFilter );
        wpcf7.addEventListener( 'wpcf7mailsent', onMailSent );
        SubmitLabel_Init();
        RequestType_Confirm();
        SubmitLabel_Confirm();
        ResponseOutputObserver();

    } );

} );

// // [test] custom event listener code example
// window.addEventListener('cm4cf7_submit_value_replaced', ev => console.log(ev));
// window.addEventListener('cm4cf7_submit_value_restored', ev => console.log(ev));
// window.addEventListener('cm4cf7_return_button_inserted', ev => console.log(ev));
// window.addEventListener('cm4cf7_confirm_value_inserted', ev => console.log(ev));
// window.addEventListener('cm4cf7_moved_to_confirm', ev => console.log(ev));
// window.addEventListener('cm4cf7_returned_to_input', ev => console.log(ev));
