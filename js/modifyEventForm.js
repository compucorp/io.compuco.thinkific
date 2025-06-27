CRM.$(function ($) {

  (function() {
   observeCustomFieldsAreDisplayed();
   if (document.querySelector('#customData_Event')) {
     (new window.MutationObserver(function () {
       observeCustomFieldsAreDisplayed();
     })).observe(document.querySelector('#customData_Event'), {
       attributes: true
     });
   }
  })();

  function observeCustomFieldsAreDisplayed() {
    const observer = new window.MutationObserver(function () {
      if ($('.custom-group-Sync_Event_to_Thinkific').length) {
        observer.disconnect();

        toggleCustomGroupFields();
        removeSpacesInCourseIdField();
        updatePlaceholder();
        $('.custom-group-Sync_Event_to_Thinkific input.crm-form-checkbox').on("change", function () {
          toggleCustomGroupFields();
        });
      }
    });

    observer.observe(document.body, {
      childList: true,
      subtree: true
    });
  }

  function toggleCustomGroupFields() {
    let syncCheckbox = $('.custom-group-Sync_Event_to_Thinkific input.crm-form-checkbox');

    let fields = [
      $("input[data-crm-custom='Sync_Event_to_Thinkific:Thinkific_Course_Id']"),
      $("select[data-crm-custom='Sync_Event_to_Thinkific:Participant_Status_to_Enroll_in_Thinkific_Course']"),
      $("select[data-crm-custom='Sync_Event_to_Thinkific:Participant_Roles_to_Enroll_in_Thinkific_Course']"),
    ];

    if (syncCheckbox.prop('checked')) {
      for (let i = 0; i < fields.length; i++) {
        if (i === 0) {
          makeTheFieldRequired(fields[i]);
        }
        fields[i].closest('.custom_field-row').show();
      }
    } else {
      for (let i = 0; i < fields.length; i++) {
        if (i === 0) {
          makeTheFieldNotRequired(fields[i]);
        }
        fields[i].closest('.custom_field-row').hide();
      }
    }
  }

  function makeTheFieldRequired($field) {
    $($field).addClass('required');
    $($field).closest('tr').find('.crm-marker').remove();
    $($field).closest('tr').find('label').eq(0).append(' <span class="crm-marker" title="This field is required.">*</span>')
  }

  function makeTheFieldNotRequired($field) {
    $($field).removeClass('required');
    $($field).closest('tr').find('.crm-marker').remove();
  }

  function updatePlaceholder() {
    $('select[data-crm-custom="Sync_Event_to_Thinkific:Participant_Roles_to_Enroll_in_Thinkific_Course"]')
      .attr('placeholder', ts('- All Roles -')).crmSelect2({
      placeholder: '- All Roles -',
      allowClear: true
    });
    $('select[data-crm-custom="Sync_Event_to_Thinkific:Participant_Status_to_Enroll_in_Thinkific_Course"]')
      .attr('placeholder', ts('- All Statuses -')).crmSelect2({
      placeholder: '- All Statuses -',
      allowClear: true
    });
  }

  function removeSpacesInCourseIdField() {
    $('input[data-crm-custom="Sync_Event_to_Thinkific:Thinkific_Course_Id"]').on('input', function() {
      this.value = this.value.replace(/\s/g, '');
    });
  }
});
