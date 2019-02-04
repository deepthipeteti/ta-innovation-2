(function ($, Drupal) {
  Drupal.behaviors.face_registration = {
    attach: function (context, settings) {

      Webcam.set({
        width: 320,
        height: 240,
        image_format: 'jpeg',
        jpeg_quality: 90
        
      });
      Webcam.attach('#webcam');

      $('#snap').click(function(e) {
        e.preventDefault();
        Webcam.snap(function(data_uri) {
          document.getElementById('file_target').value = data_uri;
          Webcam.upload( data_uri, '', function(code, text) {
            $('#webcam_image').html('<h2>Here is your image:</h2>'+'<img src="'+data_uri+'"/>');
          } );  
        });
      });

    }
  };
})(jQuery, Drupal);
