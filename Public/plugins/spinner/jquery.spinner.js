/* ==============================================================================
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */
;(function ($) {
  $.fn.spinner = function () {
    return this.each(function () {
      var opts = {min:$(this).data('min'),max:$(this).data('max')};
      var defaults = {value:1, min:0}
      var options = $.extend(defaults, opts)
      var keyCodes = {up:38, down:40}
      var container = $('<div style=" float:left;margin: -2px 5px 0px 0px;"></div>')
      container.addClass('spinner')
      var textField = $(this).addClass('value')
        .bind('keyup paste change', function (e) {
          var field = $(this)
          if (e.keyCode == keyCodes.up) changeValue(1)
          else if (e.keyCode == keyCodes.down) changeValue(-1)
          else if (getValue(field) != container.data('lastValidValue')) validateAndTrigger(field)
        })
      textField.wrap(container)
      var increaseButton = $('<button class="increase">+</button>').click(function (e) { e.preventDefault();changeValue(1) })
      var decreaseButton = $('<button class="decrease">-</button>').click(function (e) { e.preventDefault();changeValue(-1) })
      validate(textField)
      container.data('lastValidValue', options.value)
      textField.before(decreaseButton)
      textField.after(increaseButton)

      function changeValue(delta) {
        textField.val(getValue() + delta)
        var max = textField.data('max');
        var min = textField.data('min');
        var value = textField.val();
        if(parseInt(value) > parseInt(max)){
          textField.val(max);
          if(parseInt(max) == 0){
           textField.val(1);
          }
        }
        if(parseInt(value) < parseInt(min)){
          textField.val(min);
        }
        validateAndTrigger(textField)
        changePrice();
        var hyzl =textField.data('hyzl');
        var is_c = $('#zl-'+hyzl).is(':checked');
        if(is_c){
          changeGoods();
        }
      }

      function validateAndTrigger(field) {
        clearTimeout(container.data('timeout'))
        var value = validate(field)
        if (!isInvalid(value)) {
          textField.trigger('update', [field, value])
        }
      }

      function validate(field) {
        var value = getValue()
        
        field.toggleClass('invalid', isInvalid(value)).toggleClass('passive', value === 0)
        if (isInvalid(value)) {
          var timeout = setTimeout(function () {
            textField.val(container.data('lastValidValue'))
            validate(field)
          }, 500)
          container.data('timeout', timeout)
        } else {
          container.data('lastValidValue', value)
        }
        return value
      }

      function isInvalid(value) { return isNaN(+value) || value < options.min; }

      function getValue(field) {
        field = field || textField;
        var value = field.val();
        var max = textField.data('max');
        var min = textField.data('min');
        if(parseInt(value) > parseInt(max)){
          value = max;
          if(parseInt(max) == 0){
           value = 1;
          }
        }
        if(parseInt(value) < parseInt(min)){
          value = min;
        }
        return parseInt(value || 0, 10)
      }    })
  }
})(jQuery)
