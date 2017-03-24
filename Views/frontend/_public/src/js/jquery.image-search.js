(function ($, window) {

    $.plugin('swImageSearch', {

        defaults: {

            mainSearchSelector: '.search--main',

            imageSearchSelector: '.search--images',

            switchBtnSelector: '.btn--search-image',

            webCamBtnSelector: '.btn--webcam-image',

            webCamVideoSelector: '.search--webcam-video',

            imageInputSelector: '.search--image-input',

            snapShotBtnSelector: '.search--webcam-snapshot',

            fileInputSelector: '#image-upload',

            webCamMedia: {
                audio: false,
                video: true
            },

            controllerUrl: null,

            clariApiUser: 'J-NiAVGDQ2yWDurrDO54FQ_oAdDACZYuIHHLfsYL',

            clariApiSecret: '_jKoxN1a6lw16A3z8w-yvwGuDloTSRIDU1Ang3bs'
        },

        init: function () {
            var me = this;

            me.applyDataAttributes();

            if (me.opts.controllerUrl === null) {
                return false;
            }

            me.initUserMedia();
            me.initClarifaiApp();

            me.$mainSearch = me.$el.find(me.opts.mainSearchSelector);
            me.$imageSearch = me.$el.find(me.opts.imageSearchSelector);
            me.$switchBtn = me.$el.find(me.opts.switchBtnSelector);
            me.$webCamBtn = me.$el.find(me.opts.webCamBtnSelector);
            me.$webCamVideo = me.$el.find(me.opts.webCamVideoSelector);
            me.$imageInput = me.$el.find(me.opts.imageInputSelector);
            me.$snapShotBtn = me.$el.find(me.opts.snapShotBtnSelector);
            me.$fileInput = me.$el.find(me.opts.fileInputSelector);

            if (!me.hasGetUserMedia()) {
                me.$webCamBtn.hide();
            }

            me.videoEl = me.createVideoElement(400, 300);
            me.$webCamVideo.prepend(me.videoEl);

            me.registerEvents();
        },

        registerEvents: function () {
            var me = this;

            me._on(me.$switchBtn, 'click', $.proxy(me.onSwitchBtn, me));
            me._on(me.$webCamBtn, 'click', $.proxy(me.onWebCamBtn, me));
            me._on(me.$snapShotBtn, 'click', $.proxy(me.onSnapShotBtn, me));
            me._on(me.$imageInput, 'change', $.proxy(me.onImageInput, me));
            me._on(me.$fileInput, 'change', $.proxy(me.onFileInput, me));
        },

        onSwitchBtn: function (event) {
            var me = this;

            event.preventDefault();

            if (me.$switchBtn.hasClass('is--active')) {
                me.$mainSearch.show();
                me.$imageSearch.hide();
                me.$switchBtn.removeClass('is--active');
                me.$webCamVideo.hide();
                me.stopWebCamVideo();
            } else {
                me.$mainSearch.hide();
                me.$imageSearch.show();
                me.$switchBtn.addClass('is--active');
            }
        },

        onWebCamBtn: function () {
            var me = this;

            if (me.$webCamVideo.is(':visible')) {
                me.stopWebCamVideo();
                me.$webCamVideo.hide();
                return;
            }

            me.getWebCamVideo();
        },

        onSnapShotBtn: function () {
            var me = this;

            me.sendSearchRequest(me.getWebCamSnapshot());
            // me.predictByImageUrl(me.getWebCamSnapshot());
        },

        onImageInput: function () {
            var me = this,
                imageUrl = me.$imageInput.val();

            me.sendSearchRequest(imageUrl);
            // me.predictByImageUrl(imageUrl);
        },

        onFileInput: function (event) {
            var me = this,
                target = event.target;

            if (!target.files || !window.FileReader) {
                return false;
            }

            var file = target.files[0];

            if (!file.type.match('image.*')) {
                return false;
            }

            var reader = new FileReader();

            reader.onload = function () {
                me.sendSearchRequest(me.getRawImageData(reader.result));
                // me.predictByImageUrl(me.getRawImageData(reader.result));
            };

            reader.readAsDataURL(file);
        },

        loadImage: function (imageUrl) {
            var me = this,
                img = new Image();

            img.onload = function () {
                var canvas = me.createCanvasElement(img.width, img.height),
                    context = canvas.getContext('2d');

                context.drawImage(img, 0, 0);

                // console.log('Image Data', context.getImageData(0, 0, img.width, img.height));
                // console.log('Image Data URL', canvas.toDataURL('image/jpeg'));
            };

            img.src = imageUrl;
        },

        sendSearchRequest: function (imageData) {
            var me = this;

            if (!imageData) {
                return false;
            }

            $.ajax({
                url: me.opts.controllerUrl,
                method: 'POST',
                data: {
                    imageData: imageData
                }
            }).done(function (response) {
                console.log(response);
            });
        },

        getWebCamVideo: function () {
            var me = this;

            if (me.hasGetUserMedia()) {
                navigator._getUserMedia(me.opts.webCamMedia, function(mediaStream) {

                    me.mediaStream = mediaStream;

                    if (navigator.mozGetUserMedia) {
                        me.videoEl.mozSrcObject = mediaStream;
                    } else {
                        var videoURL = me.getUrlObject();
                        me.videoEl.src = videoURL.createObjectURL(mediaStream);
                    }

                    me.$webCamVideo.show();
                },

                function(error) {
                    console.error(error);
                });

            } else {
                console.warn('The getUserMedia feature is not supported in your browser.')
            }
        },

        stopWebCamVideo: function () {
            var me = this;

            if (me.mediaStream) {
                me.mediaStream.getVideoTracks().forEach(function (stream) {
                    stream.stop();
                });
            }

            me.videoEl.pause();
        },

        getWebCamSnapshot: function () {
            var me = this,
                canvas = me.createCanvasElement(me.videoEl.width, me.videoEl.height),
                context = canvas.getContext('2d');

            context.drawImage(me.videoEl, 0, 0, me.videoEl.width, me.videoEl.height);

            return me.getRawImageData(canvas.toDataURL('image/jpeg'));
        },

        getRawImageData: function (imageData) {
            return imageData.replace(/^data:image\/(png|jpg|jpeg);base64,/, '');
        },

        createCanvasElement: function (width, height) {
            var canvas = document.createElement('canvas');

            canvas.width = width || '100%';
            canvas.height = height || '100%';

            return canvas;
        },

        createVideoElement: function (width, height) {
            var video = document.createElement('video');

            video.width = width || '100%';
            video.height = height || '100%';
            video.autoplay = true;
            video.loop = true;
            video.volume = 0;
            video.controls = false;

            return video;
        },

        initUserMedia: function () {
            navigator._getUserMedia = (
            navigator.getUserMedia ||
            navigator.webkitGetUserMedia ||
            navigator.mozGetUserMedia ||
            navigator.msGetUserMedia);
        },

        hasGetUserMedia: function () {
            return !!(
            navigator.getUserMedia ||
            navigator.webkitGetUserMedia ||
            navigator.mozGetUserMedia ||
            navigator.msGetUserMedia);
        },

        getUrlObject: function () {
            return window.URL || window.webkitURL;
        },

        initClarifaiApp: function () {
            var me = this;

            me.clarifai = new Clarifai.App(
                me.opts.clariApiUser,
                me.opts.clariApiSecret
            );
        },

        predictByImageUrl: function (url) {
            var me = this,
                tags = [];

            me.clarifai.models.predict(Clarifai.GENERAL_MODEL, url).then(
                function(response) {
                    if (response.outputs) {
                        response.outputs.forEach(function (output) {
                            if (output.data.concepts) {
                                output.data.concepts.forEach(function (concept) {
                                    tags.push(concept.name);
                                });
                            }
                        });
                    }

                    console.log('Image Tags', tags);
                    console.log('Output', response.outputs);
                },
                function(err) {
                    console.error(err);
                }
            );
        }
    });

    window.StateManager.addPlugin('*[data-imageSearch="true"]', 'swImageSearch');

})(jQuery, window);