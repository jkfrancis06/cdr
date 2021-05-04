window.onload = function () {
    Vue.component('v-pagination', window['vue-plain-pagination'])

    var app = new Vue({
        el: '#app',
        delimiters: ['${', '}'],
        data: {
            edges : [],
            nodes: [],
            date_rane:''
        },

        watch: {
        },

        mounted (){

            $('input[name="daterange"]',this.$el).daterangepicker({
                timePicker: true,
                timePicker24Hour: true,
                applyButtonClasses: 'btn-primary',
                drops: 'left',
                locale: {
                    format: 'YYYY-MM-DD HH:mm:ss'
                }
            }, function(start, end, label) {
                r = start.format('YYYY-MM-DD HH:mm:ss') + '&' + end.format('YYYY-MM-DD HH:mm:ss')
                window.localStorage.setItem('date_range',r);

            });

        },

        beforeDestroy() {
            // remove pickadate according to its API
        },

        methods : {
            sendData:function (){
                window.open(href, '_blank').focus();
            }
        }
    })
}
