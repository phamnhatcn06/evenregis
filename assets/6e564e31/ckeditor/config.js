/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function (config) {
    // Define changes to default configuration here. For example:
    var host = "";

    config.language = 'vi';
    config.skin = 'office2013';

    config.filebrowserBrowseUrl = host + "assets/ckfinder/ckfinder.html";

    config.filebrowserImageBrowseUrl = host + "assets/ckfinder/ckfinder.html?type=Images";

    config.filebrowserFlashBrowseUrl = host + "assets/ckfinder/ckfinder.html?type=Flash";

    config.filebrowserUploadUrl = host + "assets/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files";

    config.filebrowserImageUploadUrl = host + "assets/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images";

    config.filebrowserFlashUploadUrl = host + "assets/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash";
};
