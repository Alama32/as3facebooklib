/*
  +---------------------------------------------------------------------------+
  | Facebook Development Platform Java Client                                 |
  +---------------------------------------------------------------------------+
  | Copyright (c) 2007 Facebook, Inc.                                         |
  | All rights reserved.                                                      |
  |                                                                           |
  | Redistribution and use in source and binary forms, with or without        |
  | modification, are permitted provided that the following conditions        |
  | are met:                                                                  |
  |                                                                           |
  | 1. Redistributions of source code must retain the above copyright         |
  |    notice, this list of conditions and the following disclaimer.          |
  | 2. Redistributions in binary form must reproduce the above copyright      |
  |    notice, this list of conditions and the following disclaimer in the    |
  |    documentation and/or other materials provided with the distribution.   |
  |                                                                           |
  | THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR      |
  | IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES |
  | OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.   |
  | IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT,          |
  | INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT  |
  | NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, |
  | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY     |
  | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT       |
  | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF  |
  | THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.         |
  +---------------------------------------------------------------------------+
  | For help with this library, contact developers-help@facebook.com          |
  +---------------------------------------------------------------------------+
 */
package com.facebook.api;

import java.util.ArrayList;
import java.util.Collection;
import java.util.EnumMap;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

public enum FacebookParam
  implements CharSequence {
  SIGNATURE,
  USER("user"),
  SESSION_KEY("session_key"),
  EXPIRES("expires"),
  IN_CANVAS("in_canvas"),
  IN_IFRAME("in_iframe"),
  IN_PROFILE("profile"),
  TIME("time"),
  ;

  private static Map<String, FacebookParam> _lookupTable =
    new HashMap<String, FacebookParam>(FacebookParam.values().length);
  static {
    for (FacebookParam param: FacebookParam.values()) {
      _lookupTable.put(param.toString(), param);
    }
  }

  /**
   * Retrieves the FacebookParam corresponding to the supplied String key.
   * @param key a possible FacebookParam
   * @return the matching FacebookParam or null if there's no match
   */
  public static FacebookParam get(String key) {
    return isInNamespace(key) ? _lookupTable.get(key) : null;
  }

  /**
   * Indicates whether a given key is in the FacebookParam namespace
   * @param key
   * @return boolean 
   */
  public static boolean isInNamespace(String key) {
    return null != key && key.startsWith(FacebookParam.SIGNATURE.toString());
  }
  
  public static boolean isSignature(String key) {
    return SIGNATURE.equals(get(key));
  }

  private String _paramName;

  private FacebookParam() {
    this._paramName = "fb_sig";
  }

  private FacebookParam(String name) {
    this._paramName = "fb_sig_" + name;
  }

  /* Implementing CharSequence */
  public char charAt(int index) {
    return this._paramName.charAt(index);
  }

  public int length() {
    return this._paramName.length();
  }

  public CharSequence subSequence(int start, int end) {
    return this._paramName.subSequence(start, end);
  }

  public String toString() {
    return this._paramName;
  }
  
  public static void main(String[] args) {
    System.out.println( isSignature("fb_sig") );
    System.out.println( !isSignature("fb_sig_something") );
    
    assert false;
  }
}