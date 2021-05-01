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
                first: 'Premier',
                prev: 'Precedent',
                next: 'Suivant',
                last: 'Dernier'
            },

            //
            communication_array_def: [],
            communication_array: [],
            number_filter_result: [],
            pagination_array: [],
            number_to_filter: '',

            date_time_range_com : {
                range: '',
                start: '',
                end: ''
            },

        },

        watch: {
            currentPage: 'paginate_table'
        },

        mounted (){

            let a = new Date("2020-03-01 15:58:00")
            let b = new Date("2020-03-01 03:58:00")

            if (a>b) {
                console.log(true)
            }else {
                console.log(false)
            }

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
                        format: 'YYYY-MM-DD HH:mm:ss'
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

                $('input[name="daterange_com"]',this.$el).daterangepicker({
                    timePicker: true,
                    startDate: moment().startOf('hour'),
                    endDate: moment().startOf('hour').add(32, 'hour'),
                    timePicker24Hour: true,
                    applyButtonClasses: 'btn-primary',
                    drops: 'up',
                    locale: {
                        format: 'YYYY-MM-DD HH:mm:ss'
                    }
                }, function(start, end, label) {
                    self.number_filter_result = self.communication_array.filter(function(communication) {
                        self.date_time_range_com.range = start.format('YYYY-MM-DD HH:mm:ss') + ' - ' + end.format('YYYY-MM-DD HH:mm:ss');
                        self.date_time_range_com.start =  new Date(start.format('YYYY-MM-DD HH:mm:ss'))
                        self.date_time_range_com.end =  new Date(end.format('YYYY-MM-DD HH:mm:ss'))
                        return (self.date_time_range_com.start <= new Date(communication.dayTime) && new Date(communication.dayTime) <= self.date_time_range_com.end) && self.filter(communication,self.number_to_filter)
                    });
                    self.paginate_table();
                });



            });

            this.user.number = document.getElementById("c_number").innerHTML
            self = this
            try {
                axios.get('/number/commnunications/'+this.user.number)
                    .then(function (response){
                        self.frequent_users = response.data["fav_numbers_array"]
                        self.communication_array = response.data["com_list"]
                        self.communication_array_def = response.data["com_list"]
                        self.number_filter_result = response.data["com_list"]
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
                this.pagination_array = this.number_filter_result.slice((this.currentPage - 1) * this.pageSize, this.currentPage * this.pageSize);
                this.totalPages = Math.ceil(this.number_filter_result.length / this.pageSize)
            },

            convert_to_hour:function (duration){
                return new Date(duration * 1000).toISOString().substr(11, 8)
            },

            filter_by_date:function (communication){
                return this.date_time_range_com.start <= new Date(communication.dayTime) && new Date(communication.dayTime) <= this.date_time_range_com.end
            },

            filter: function (communication, number_to_filter){
                return communication.numB.includes(number_to_filter)
            },

            filter_by_number: function (){
                let self = this
                this.number_filter_result = this.communication_array.filter(function(communication) {
                    if (self.date_time_range_com.start === ''){
                        return communication.numB.includes(self.number_to_filter)
                    }else {
                        return communication.numB.includes(self.number_to_filter) && self.filter_by_date(communication)
                    }
                });
                this.paginate_table()
            }
        }
    })
}