jQuery(document)
  .ready(($) => {
    const form = $('#settingsForm');
    const saveButton = $('#saveButton');
    const testButton = $('#testButton');
    const editButton = $('#editButton');
    const cancelButton = $('#cancelButton');
    const statusMessageBlock = $('#statusMessage');
    const wpAdminCheckbox = $('#zv_protect_wp_admin');
    const tokenField = $('#zv_token');
    const optionsBlock = $('#optionsBlock');
    const buttonsBlock = $('#buttonsBlock');

    const ZV_TEST_CONNECTION_ACTION = 'zv_test_connection';

    /** @param {boolean} value */
    function setEditParam(value) {
      const url = new URL(location.href);
      const hasEdit = url.searchParams.has('edit');
      if (value) {
        if (!hasEdit) {
          url.searchParams.set('edit', 'true');
        }

      } else if (hasEdit) {
        url.searchParams.delete('edit');
      }
      history.replaceState(null, null, url.toString());
    }

    function toggleElements() {
      const url = new URL(location.href);
      const isEdit = url.searchParams.get('edit') === 'true';
      if (isEdit) {
        tokenField.prop('disabled', false);
        editButton.addClass('hidden');
      } else {
        tokenField.prop('disabled', true);
        editButton.removeClass('hidden');
      }
      testButton.toggleClass('hidden', !isEdit);
      buttonsBlock.toggleClass('hidden', !isEdit);
      if (tokenField.val()
        .trim().length > 0) {
        optionsBlock.toggleClass('hidden', isEdit);
      }
    }

    function sendAjax(data, action) {
      data = {
        ...data,
        action,
        security: zv_settings_data.security,
      };
      return $.post(zv_settings_data.ajax_url, data);
    }

    function doStatusCodeActions(code, message) {
      const checked = wpAdminCheckbox.prop('checked');
      const url = new URL(location.href);
      const isEdit = url.searchParams.get('edit') === 'true';
      let statusText = `${message}`;
      const notEditSuccessMessageOn = 'zenVPN plugin is running correctly. Your wp-admin is protected now!';
      const notEditSuccessMessageOff = 'Settings successfully saved.';
      const notEditFailureMessage = 'Unable to save settings';
      switch (code) {
        case 200:
          if (!isEdit) {
            statusText = checked ? notEditSuccessMessageOn : notEditSuccessMessageOff;
          }
          statusMessageBlock.addClass('success')
            .removeClass('warning error hidden');
          break;

        case 204:
        case 404:
          statusMessageBlock.addClass('warning')
            .removeClass('success error hidden');
          if (!isEdit) {
            statusText = notEditFailureMessage;
            statusMessageBlock.addClass('error')
              .removeClass('success warning hidden');
          }
          break;

        case 403:
          statusMessageBlock.addClass('error')
            .removeClass('warning success hidden');
          break;

        default:
          statusMessageBlock.addClass('error')
            .removeClass('warning success hidden');
          if (!isEdit) {
            statusText = notEditFailureMessage;
          }
          break;
      }
      statusMessageBlock.html(statusText);
    }

    function handleAjax(response) {
      const url = window.location.href;
      const isEdit = url.indexOf('edit=true') !== -1;
      const { data } = response;
      if (data) {
        const {
          code,
          message,
        } = data;
        if (code) {
          if (isEdit && (code === 200 || code === 204)) {
            saveButton.prop('disabled', false);
          }

          doStatusCodeActions(code, message);
        }
      }
    }

    function handleAjaxError(response) {
      const { data } = response.responseJSON;
      if (data) {
        const {
          code,
          message,
        } = data;
        if (code) {
          doStatusCodeActions(code, message);
        }
      }
    }

    function loadTokenValue() {
      sendAjax({}, 'zv_load_token_value')
        .done((response) => {
          tokenField.val(response.data);
        })
        .fail(handleAjaxError);
    }

    function checkPageState() {
      const url = new URL(location.href);
      const hasEdit = url.searchParams.has('edit');
      setEditParam(hasEdit);
      toggleElements();
    }

    function clearStatusMessage() {
      statusMessageBlock.html('')
        .addClass('hidden');
    }

    saveButton.on('click', () => {
      clearStatusMessage();

      const formData = form.serializeArray();

      const settingsData = formData.reduce((acc, item) => {
        if (item.name.includes('zv_settings')) {
          acc[item.name] = item.value;
        }
        return acc;
      }, {});

      sendAjax(settingsData, 'zv_save_plugin_settings')
        .done(handleAjax)
        .fail(handleAjaxError);

      setEditParam(false);
      toggleElements();
    });

    testButton.on('click', () => {
      clearStatusMessage();
      const token = tokenField.val();

      sendAjax({ token }, ZV_TEST_CONNECTION_ACTION)
        .done(handleAjax)
        .fail(handleAjaxError);
    });

    editButton.on('click', () => {
      clearStatusMessage();
      setEditParam(true);
      toggleElements();
    });

    cancelButton.on('click', () => {
      clearStatusMessage();
      setEditParam(false);
      toggleElements();
      loadTokenValue();
    });

    wpAdminCheckbox.on('change', ({target: input}) => {
      const checked = input.checked;

      const tokenValue = tokenField.val();
      const wpAdminValue = checked ? 1 : 0;

      const settingsData = {};
      settingsData['zv_settings[token]'] = tokenValue;
      settingsData['zv_settings[zv_protect_wp_admin]'] = wpAdminValue;

      sendAjax({ token: tokenValue }, ZV_TEST_CONNECTION_ACTION)
        .done((response) => {
          if (response.data.code === 200) {
            sendAjax(settingsData, 'zv_save_plugin_settings').done(handleAjax).fail(handleAjaxError);
          } else {
            input.checked = !checked;
          }
        }, handleAjax)
        .fail(handleAjaxError);
    });

    tokenField.on('input', () => {
      saveButton.prop('disabled', true);
    });

    checkPageState();
  });
