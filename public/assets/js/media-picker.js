/**
 * KayaCMS Media Picker helper.
 *
 * Usage inside an opener page (admin content form with CKEditor):
 *
 *   window.kayaCmsMediaSelect = function (media) {
 *       // media = { url, alt, type }
 *       if (window.CKEDITOR) {
 *           window.CKEDITOR.instances['body'].insertHtml(
 *               media.type === 'image'
 *                   ? '<figure><img src="' + media.url + '" alt="' + media.alt + '"></figure>'
 *                   : '<a href="' + media.url + '">' + (media.alt || media.url) + '</a>'
 *           );
 *       } else {
 *           window.kayaCmsMediaSelection = media;
 *       }
 *   };
 *
 *   // Open picker window
 *   window.kayaCmsOpenMediaPicker = function () {
 *       var w = window.open('/admin/media/picker', 'kayaCmsMediaPicker', 'width=1000,height=700');
 *       w.focus();
 *   };
 */
(function () {
    window.kayaCmsMediaSelect = window.kayaCmsMediaSelect || null;
    window.kayaCmsOpenMediaPicker = function () {
        window.open('/admin/media/picker', 'kayaCmsMediaPicker', 'width=1000,height=700,scrollbars=yes');
    };
})();