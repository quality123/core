if (typeof dav == 'undefined') { dav = {}; };

dav.Client = function(options) {
    var i;
    for(i in options) {
        this[i] = options[i];
    }

};

dav.Client.prototype = {

    baseUrl : null,

    userName : null,

    password : null,


    xmlNamespaces : {
        'DAV:' : 'd'
    },

    /**
     * Generates a propFind request.
     *
     * @param {string} url Url to do the propfind request on
     * @param {Array} properties List of properties to retrieve.
     * @return {Promise}
     */
    propFind : function(url, properties, depth) {

        if(typeof depth == "undefined") {
            depth = 0;
        }

        var headers = {
            Depth          : depth,
            'Content-Type' : 'application/xml; charset=utf-8'
        };

        var body =
            '<?xml version="1.0"?>\n' +
            '<d:propfind ';
        var namespace;
        for (namespace in this.xmlNamespaces) {
            body += ' xmlns:' + this.xmlNamespaces[namespace] + '="' + namespace + '"';
        }
        body += '>\n' +
            '  <d:prop>\n';

        for(var ii in properties) {

            var property = this.parseClarkNotation(properties[ii]);
            if (this.xmlNamespaces[property.namespace]) {
                body+='    <' + this.xmlNamespaces[property.namespace] + ':' + property.name + ' />\n';
            } else {
                body+='    <x:' + property.name + ' xmlns:x="' + property.namespace + '" />\n';
            }

        }
        body+='  </d:prop>\n';
        body+='</d:propfind>';

        return this.request('PROPFIND', url, headers, body).then(
            function(result) {

                var resultBody = this.parseMultiStatus(result.body);
                if (depth===0) {
                    return {
                        status: result.status,
                        body: resultBody[0],
                        xhr: result.xhr
                    };
                } else {
                    return {
                        status: result.status,
                        body: resultBody,
                        xhr: result.xhr
                    };
                }

            }.bind(this)
        );

    },

    /**
     * Performs a HTTP request, and returns a Promise
     *
     * @param {string} method HTTP method
     * @param {string} url Relative or absolute url
     * @param {Object} headers HTTP headers as an object.
     * @param {string} body HTTP request body.
     * @return {Promise}
     */
    request : function(method, url, headers, body) {

        var xhr = this.xhrProvider();

        if (this.userName) {
            headers['Authorization'] = 'Basic ' + btoa(this.userName + ':' + this.password);
            // xhr.open(method, this.resolveUrl(url), true, this.userName, this.password);
        }
        xhr.open(method, this.resolveUrl(url), true);
        var ii;
        for(ii in headers) {
            xhr.setRequestHeader(ii, headers[ii]);
        }
        xhr.send(body);

        return new Promise(function(fulfill, reject) {

            xhr.onreadystatechange = function() {

                if (xhr.readyState !== 4) {
                    return;
                }

                fulfill({
                    body: xhr.response,
                    status: xhr.status,
                    xhr: xhr
                });

            };

            xhr.ontimeout = function() {

                reject(new Error('Timeout exceeded'));

            };

        });

    },

    /**
     * Returns an XMLHttpRequest object.
     *
     * This is in its own method, so it can be easily overridden.
     *
     * @return {XMLHttpRequest}
     */
    xhrProvider : function() {

        return new XMLHttpRequest();

    },


    /**
     * Parses a multi-status response body.
     *
     * @param {string} xmlBody
     * @param {Array}
     */
    parseMultiStatus : function(xmlBody) {

        var parser = new DOMParser();
        var doc = parser.parseFromString(xmlBody, "application/xml");

        var resolver = function(foo) {
            var ii;
            for(ii in this.xmlNamespaces) {
                if (this.xmlNamespaces[ii] === foo) {
                    return ii;
                }
            }
        }.bind(this);

        var responseIterator = doc.evaluate('/d:multistatus/d:response', doc, resolver);

        var result = [];
        var responseNode = responseIterator.iterateNext();

        while(responseNode) {

            var response = {
                href : null,
                propStat : []
            };

            response.href = doc.evaluate('string(d:href)', responseNode, resolver).stringValue;

            var propStatIterator = doc.evaluate('d:propstat', responseNode, resolver);
            var propStatNode = propStatIterator.iterateNext();

            while(propStatNode) {

                var propStat = {
                    status : doc.evaluate('string(d:status)', propStatNode, resolver).stringValue,
                    properties : [],
                };

                var propIterator = doc.evaluate('d:prop/*', propStatNode, resolver);

                var propNode = propIterator.iterateNext();
                while(propNode) {
                    var content = propNode.textContent;
                    if (!content && propNode.hasChildNodes()) {
                        content = propNode.childNodes;
                    }

                    propStat.properties['{' + propNode.namespaceURI + '}' + propNode.localName] = content;
                    propNode = propIterator.iterateNext();

                }
                response.propStat.push(propStat);
                propStatNode = propStatIterator.iterateNext();


            }

            result.push(response);
            responseNode = responseIterator.iterateNext();

        }

        return result;

    },

    /**
     * Takes a relative url, and maps it to an absolute url, using the baseUrl
     *
     * @param {string} url
     * @return {string}
     */
    resolveUrl : function(url) {

        // Note: this is rudamentary.. not sure yet if it handles every case.
        if (/^https?:\/\//i.test(url)) {
            // absolute
            return url;
        }

        var baseParts = this.parseUrl(this.baseUrl);
        if (url.charAt('/')) {
            // Url starts with a slash
            return baseParts.root + url;
        }

        // Url does not start with a slash, we need grab the base url right up until the last slash.
        var newUrl = baseParts.root + '/';
        if (baseParts.path.lastIndexOf('/')!==-1) {
            newUrl = newUrl = baseParts.path.subString(0, baseParts.path.lastIndexOf('/')) + '/';
        }
        newUrl+=url;
        return url;

    },

    /**
     * Parses a url and returns its individual components.
     *
     * @param {String} url
     * @return {Object}
     */
    parseUrl : function(url) {

         var parts = url.match(/^(?:([A-Za-z]+):)?(\/{0,3})([0-9.\-A-Za-z]+)(?::(\d+))?(?:\/([^?#]*))?(?:\?([^#]*))?(?:#(.*))?$/);
         var result = {
             url : parts[0],
             scheme : parts[1],
             host : parts[3],
             port : parts[4],
             path : parts[5],
             query : parts[6],
             fragment : parts[7],
         };
         result.root =
            result.scheme + '://' +
            result.host +
            (result.port ? ':' + result.port : '');

         return result;

    },

    parseClarkNotation : function(propertyName) {

        var result = propertyName.match(/^{([^}]+)}(.*)$/);
        if (!result) {
            return;
        }

        return {
            name : result[2],
            namespace : result[1]
        };

    }

};

