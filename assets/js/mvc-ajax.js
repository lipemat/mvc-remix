/**
 * Ajax interaction for the Mvc Theme Plugin
 * 
 * @since 0.1.0
 * @uses MvcAjax.Request(%controller%, %method%, %data%);
 * 
 * @author Mat Lipe <mat@matlipe.com>
 */


/**
 * Main object for ajax interaction
 * 
 * @since 10.2.13
 * @uses MvcAjax.Request(%controller%, %method%, %data%); 
 */
var MvcAjax = {
    
    //holds the request data
    data :{},
    
    /**
     * Make an ajax request to a MVC Controller
     * 
     * @param string controller - controller to run
     * @param string method - method on the controller
     * @param {Object} data - data to pass to the method
     */
    Request : function(controller, method, data ){
        
          
          data = this.MakeData( controller, method, data );
        
        
           $.post(MVCAjaxData.URL, data, function(response) {
                
                alert( response );

                var Data = $.parseJSON ( response );
                // Go through each element in the object
                for( prop in Data ){
                 //   alert( Data [prop] );            
                }
          });

        
    },
    
    
    /**
     * Turn the passed args into one data object to be sent
     * @param {Object} data
     * 
     * @uses used by MvcAjax.Request
     */
    MakeData : function(controller, method, data){
        
        this.data = {};
        
        this.data.controller = controller;
        this.data.method = method;
        this.data.args = data;
        
        return this.data;
    },
    
    
    

    

}