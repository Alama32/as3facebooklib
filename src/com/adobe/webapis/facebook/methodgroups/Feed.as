/*
Adobe Systems Incorporated(r) Source Code License Agreement
Copyright(c) 2005 Adobe Systems Incorporated. All rights reserved.
	
Please read this Source Code License Agreement carefully before using
the source code.
	
Adobe Systems Incorporated grants to you a perpetual, worldwide, non-exclusive,
no-charge, royalty-free, irrevocable copyright license, to reproduce,
prepare derivative works of, publicly display, publicly perform, and
distribute this source code and such derivative works in source or
object code form without any attribution requirements.
	
The name "Adobe Systems Incorporated" must not be used to endorse or promote products
derived from the source code without prior written permission.
	
You agree to indemnify, hold harmless and defend Adobe Systems Incorporated from and
against any loss, damage, claims or lawsuits, including attorney's
fees that arise or result from your use or distribution of the source
code.
	
THIS SOURCE CODE IS PROVIDED "AS IS" AND "WITH ALL FAULTS", WITHOUT
ANY TECHNICAL SUPPORT OR ANY EXPRESSED OR IMPLIED WARRANTIES, INCLUDING,
BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
FOR A PARTICULAR PURPOSE ARE DISCLAIMED. ALSO, THERE IS NO WARRANTY OF
NON-INFRINGEMENT, TITLE OR QUIET ENJOYMENT. IN NO EVENT SHALL MACROMEDIA
OR ITS SUPPLIERS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS;
OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR
OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOURCE CODE, EVEN IF
ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

package com.adobe.webapis.facebook.methodgroups {
	
	import com.adobe.webapis.facebook.events.FacebookResultEvent;
	import com.adobe.webapis.facebook.*;
	import flash.events.Event;
	import flash.net.URLLoader;
	
		/**
		 * Broadcast as a result of the publishStoryToUser method being called
		 *
		 * The event contains the following properties
		 *	success	- Boolean indicating if the call was successful or not
		 *	data - When success is true, contains a "fbml_publishStoryToUser_response" string
		 *		   When success is false, contains an "error" FacebookError instance
		 *
		 * @see #publishStoryToUser
		 * @see com.adobe.service.facebook.FacebookError
		 * @langversion ActionScript 3.0
		 * @playerversion Flash 8.5
		 * @tiptext
		 */
		[Event(name="feedPublishStoryToUser", 
			 type="com.adobe.webapis.facebook.events.FacebookResultEvent")]
		
		/**
		 * Broadcast as a result of the publishActionOfUser method being called
		 *
		 * The event contains the following properties
		 *	success	- Boolean indicating if the call was successful or not
		 *	data - When success is true, contains a "fbml_publishActionOfUser_response" string
		 *		   When success is false, contains an "error" FacebookError instance
		 *
		 * @see #publishActionOfUser
		 * @see com.adobe.service.facebook.FacebookError
		 * @langversion ActionScript 3.0
		 * @playerversion Flash 8.5
		 * @tiptext
		 */
		[Event(name="feedPublishActionOfUser", 
			 type="com.adobe.webapis.facebook.events.FacebookResultEvent")]
		
	/**
	 * Contains the methods for the Feed method group in the Facebook API.
	 * 
	 * Even though the events are listed here, they're really broadcast
	 * from the FacebookService instance itself to make using the service
	 * easier.
	 */
	public class Feed {
			 
		/** 
		 * A reference to the FacebookService that contains the api key
		 * and logic for processing API calls/responses
		 */
		private var _service:FacebookService;
	
		/**
		 * Construct a new Feed "method group" class
		 *
		 * @param service The FacebookService this method group
		 *		is associated with.
		 * @langversion ActionScript 3.0
		 * @playerversion Flash 8.5
		 * @tiptext
		 */
		public function Feed( service:FacebookService ) {
			_service = service;
		}
		
		/**
		 * Publishes a News Feed story to the user corresponding to the session_key parameter.
		 * 
		 * @see http://developers.facebook.com/documentation.php?v=1.0&method=fbml.publishStoryToUser
		 * @langversion ActionScript 3.0
		 * @playerversion Flash 8.5
		 * @tiptext
		 */
		public function publishStoryToUser():void {
			// Let the Helper do the work to invoke the method			
			MethodGroupHelper.invokeMethod( _service, publishStoryToUser_result, "facebook.feed.publishStoryToUser", true );
		}
		
		/**
		 * Capture the result of the publishStoryToUser call, and dispatch
		 * the event to anyone listening.
		 *
		 * @param event The complete event generated by the URLLoader
		 * 			that was used to communicate with the Facebook API
		 *			from the invokeMethod method in MethodGroupHelper
		 */
		private function publishStoryToUser_result( event:Event ):void {
			// Create an FEED_PUBLISH_STORY_TO_USER event
			var result:FacebookResultEvent = new FacebookResultEvent( FacebookResultEvent.FEED_PUBLISH_STORY_TO_USER );

			// Have the Helper handle parsing the result from the server - get the data
			// from the URLLoader which correspondes to the result from the API call
			MethodGroupHelper.processAndDispatch( _service, 
												  URLLoader( event.target ).data, 
												  result,
												  "feed_publishStoryToUser_response",
												  MethodGroupHelper.parseFeedPublishStoryToUser );
		}
		
		/**
		 * Publishes a Mini-Feed story to the user corresponding to the session_key parameter, and publishes 
		 * News Feed stories to the friends of that user.
		 * 
		 * @see http://developers.facebook.com/documentation.php?v=1.0&method=fbml.publishActionOfUser
		 * @langversion ActionScript 3.0
		 * @playerversion Flash 8.5
		 * @tiptext
		 */
		public function publishActionOfUser():void {
			// Let the Helper do the work to invoke the method			
			MethodGroupHelper.invokeMethod( _service, publishActionOfUser_result, "facebook.feed.publishActionOfUser", true );
		}
		
		/**
		 * Capture the result of the publishActionOfUser call, and dispatch
		 * the event to anyone listening.
		 *
		 * @param event The complete event generated by the URLLoader
		 * 			that was used to communicate with the Facebook API
		 *			from the invokeMethod method in MethodGroupHelper
		 */
		private function publishActionOfUser_result( event:Event ):void {
			// Create an FEED_PUBLISH_STORY_TO_USER event
			var result:FacebookResultEvent = new FacebookResultEvent( FacebookResultEvent.FEED_PUBLISH_ACTION_OF_USER );

			// Have the Helper handle parsing the result from the server - get the data
			// from the URLLoader which correspondes to the result from the API call
			MethodGroupHelper.processAndDispatch( _service, 
												  URLLoader( event.target ).data, 
												  result,
												  "feed_publishActionOfUser_response",
												  MethodGroupHelper.parseFeedPublishActionOfUser );
		}

	}	
	
}