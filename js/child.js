
                  /**
                   * The Js for the Child Theme
                   * @author Mat Lipe
                   *  
                   */

jQuery(document).ready(function ( $) {
 
       //$('ul>li:last-child').addClass('last');
      //$('ul>li:first-child').addClass('first');
 
     
    
        //  if( $('#tabs').length != 0 ){
      //Initiate tabbed section
           //      $( "#tabs" ).tabs({
          //             fx: { opacity: 'toggle' }  
          //      });
         //   }

        //Make the Word Free Green
        //$('#text-11 h4, #text-13 h4, #vimm-teaser h4').each( function(){
            // $(this).html($(this).html().replace('FREE','<span class="green">FREE</span>') ); 
        //});
        
        
     if( $('#slides').length > 0 ){
        $('#slides').slides({
            preload: true,
            generateNextPrev: false,
            play: 4000,  //length of time a slide shows
            preloadImage: '/wp-includes/images/blank.gif' ,
            preload: true,
            generatePagination: false,
            slideSpeed: 5000,
            fadeSpeed: 2000,
            effect: 'fade',
            crossfade: true,
            randomize: false
        });
    }
        

});



function show_object( obj ){
        var str ="" ; //variable which will hold property values
        for(prop in obj )
                {
                 str+=prop + " value: "+ obj [prop]+"\n" ;//Concate prop and its value from object
                 }
       alert( str);
}


