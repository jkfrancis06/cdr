window.onload = function () {
    Vue.component('v-pagination', window['vue-plain-pagination'])

    var app = new Vue({
        el: '#number-app',
        delimiters: ['${', '}'],
        data: {
            user: {
                c_name : '',
                c_operator : '',
                c_number : '',
                c_file_name : ''
            },
            frequent_user: {
                num_b: '',
                duration: '',
                nb: ''
            },
            frequent_users: [],
            date_time_range : {
                range: '',
                start: '',
                end: ''
            },

            // pagination
            currentPage: 1,
            totalPages: 1,
            pageSize: 15,
            bootstrapPaginationClasses: {
                ul: 'uk-pagination uk-flex-center',
                li: 'page-item',
                liActive: 'uk-active',
                liDisable: 'uk-disabled',
                button: 'page-link'
            },
            paginationAnchorTexts: {
                first: 'First',
                prev: 'Previous',
                next: 'Next',
                last: 'Last'
            },

            //
            communication_array: [],
            pagination_array: [],

        },

        watch: {
            currentPage: 'paginate_table'
        },

        mounted (){

            // loader
            UIkit.modal('#modal-center', {'bgClose':false}).show();

            // load date picker
            self = this
            $(function() {
                $('input[name="daterange"]',this.$el).daterangepicker({
                    timePicker: true,
                    startDate: moment().startOf('hour'),
                    endDate: moment().startOf('hour').add(32, 'hour'),
                    timePicker24Hour: true,
                    applyButtonClasses: 'btn-primary',
                    drops: 'up',
                    locale: {
                        format: 'YYYY/M/DD HH:mm:ss'
                    }
                }, function(start, end, label) {
                    console.log("A new date selection was made: " + start.format('YYYY-MM-DD HH:mm:ss') + ' to ' + end.format('YYYY-MM-DD HH:mm:ss'));
                    self.date_time_range.range = start.format('YYYY-MM-DD HH:mm:ss') + ' - ' + end.format('YYYY-MM-DD HH:mm:ss');
                    self.date_time_range.start = start.format('YYYY-MM-DD HH:mm:ss');
                    self.date_time_range.end = end.format('YYYY-MM-DD HH:mm:ss')
                    console.log(self.date_time_range)

                    // execute request

                    UIkit.modal('#modal-center', {'bgClose':false}).show();

                    axios({
                        method: 'post',
                        url: '/number/commnunications-details/date',
                        responseType: 'json',
                        data: {
                            'c_number': document.getElementById("c_number").innerHTML,
                            'start': self.date_time_range.start,
                            'end': self.date_time_range.end
                        }
                    }).then(function (response){
                        console.log(response)
                        modal = UIkit.modal('#modal-center', {'bgClose':false}).hide();
                        self.frequent_users = response.data
                    });


                });
            });

            this.user.number = document.getElementById("c_number").innerHTML
            self = this
            try {
                axios.get('/number/commnunications/'+this.user.number)
                    .then(function (response){
                        self.frequent_users = response.data["fav_numbers_array"]
                        self.communication_array = response.data["com_list"]
                        self.totalPages = Math.ceil(self.communication_array.length / self.pageSize)
                        console.log(self.totalPages)
                        self.paginate_table();
                        UIkit.modal('#modal-center', {'bgClose':false}).hide();
                        console.log(response)
                    })
            }catch (error){
                console.log(error.response)
            }
        },

        beforeDestroy() {
            // remove pickadate according to its API
        },

        methods : {
            paginate_table:function(){
                this.pagination_array = this.communication_array.slice((this.currentPage - 1) * this.pageSize, this.currentPage * this.pageSize);
                console.log(this.pagination_array);
            },

            convert_to_hour:function (duration){
                return new Date(duration * 1000).toISOString().substr(11, 8)
            }
        }
    })
}