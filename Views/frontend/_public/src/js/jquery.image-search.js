(function ($, window) {

    $.plugin('swImageSearch', {

        defaults: {

            controllerUrl: null

        },

        init: function () {
            var me = this;

            me.initUserMedia();

            me.$mainSearch = me.$el.find('.search--main');
            me.$imageSearch = me.$el.find('.search--images');
            me.$switchBtn = me.$el.find('.btn--search-image');
            me.$webCamBtn = me.$el.find('.btn--webcam-image');
            me.$webCamVideo = me.$el.find('.search--webcam-video');
            me.$imageInput = me.$el.find('.search--image-input');

            me.videoEl = me.createVideoElement(400, 300);
            me.$webCamVideo.append(me.videoEl);

            me.registerEvents();
        },

        registerEvents: function () {
            var me = this;

            me._on(me.$switchBtn, 'click', $.proxy(me.onSwitchBtn, me));
            me._on(me.$webCamBtn, 'click', $.proxy(me.onWebCamBtn, me));
            me._on(me.$imageInput, 'keyup', $.proxy(me.onImageInput, me));
            me._on(me.$imageInput, 'change', $.proxy(me.onImageInput, me));
        },

        onSwitchBtn: function (event) {
            var me = this;

            event.preventDefault();

            if (me.$switchBtn.hasClass('is--active')) {
                me.$mainSearch.show();
                me.$imageSearch.hide();
                me.$switchBtn.removeClass('is--active');
            } else {
                me.$mainSearch.hide();
                me.$imageSearch.show();
                me.$switchBtn.addClass('is--active');
            }
        },

        onWebCamBtn: function () {
            var me = this;

            me.getWebCamVideo();
        },

        onImageInput: function () {
            var me = this,
                imageUrl = me.$imageInput.val();

            console.log('onImageInput', imageUrl);

            me.loadImage(imageUrl);
        },

        loadImage: function (imageUrl) {
            var me = this,
                img = new Image();

            img.onload = function () {
                var canvas = me.createCanvasElement(img.width, img.height),
                    context = canvas.getContext('2d');

                context.drawImage(img, 0, 0);

                console.log('Image Data', context.getImageData(0, 0, img.width, img.height));
                // console.log('Image Data URL', canvas.toDataURL('image/jpeg'));
            };

            img.src = imageUrl;
        },

        getWebCamVideo: function () {
            var me = this;

            if (me.hasGetUserMedia()) {
                navigator._getUserMedia(
                    { audio: false, video: true },
                    function(mediaSteam) {

                        if (navigator.mozGetUserMedia) {
                            me.videoEl.mozSrcObject = mediaSteam;
                        } else {
                            var videoURL = me.getUrlObject();
                            me.videoEl.src = videoURL.createObjectURL(mediaSteam);
                        }

                        me.$webCamVideo.show();

                        me.getWebCamSnapshot();
                    },
                    function(error) {
                        console.error(error);
                    });
            } else {
                console.warn('getUserMedia is not supported in your browser.')
            }
        },

        getWebCamSnapshot: function () {
            var me = this,
                canvas = me.createCanvasElement(me.videoEl.width, me.videoEl.height),
                context = canvas.getContext('2d');

            context.drawImage(me.videoEl, 0, 0, me.videoEl.width, me.videoEl.height);

            console.log('Image Data', context.getImageData(0, 0, me.videoEl.width, me.videoEl.height));
            // console.log('Image Data URL', canvas.toDataURL('image/jpeg'));
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
        }
    });

    window.StateManager.addPlugin('*[data-imageSearch="true"]', 'swImageSearch', [ 'm', 'l', 'xl' ]);

})(jQuery, window);