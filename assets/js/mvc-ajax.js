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
     * @param Object data - data to pass to the method
     * @param [function|method] - function to call using the ajax response
     */
    Request : function(controller, method, data, funcToCall){
          
          this.returnedData = false;
          
          data = this.MakeData( controller, method, data );
          
          return jQuery.post(EdSpireAjaxData.URL, data, function(response) {  
              if( typeof( funcToCall ) !== 'undefined' ){
                 funcToCall( response ); 
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