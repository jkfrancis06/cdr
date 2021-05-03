window.onload = function () {

    var app = new Vue({
        el: '#app',
        delimiters: ['${', '}'],
        data: {
            nb_num : 1,
            p_number: '',
            operator: '',
            link: '',
            data_push: [],
            error_mngt:{
                input_error: 0,
                message: 'Le numéro de telephone incorrect',
            },
            upload_error: 0,
            upload_success: 0,
            success_data: {
                'nb_rows': '',
                'mem_usage': '',
                'file_name': '',
                'path': ''
            },
            recap_status: 0
        },


        mounted (){

            // get stored data

            let bar = document.getElementById('js-progressbar');

            let self = this

            self.upload_error = 0
            self.upload_success = 0

            UIkit.upload('.js-upload', {
                url: "/wizard/cdr-upload",
                multiple: false,
                name: 'file',
                concurrent: 1,
                method: 'POST',
                type: 'JSON',
                allow:'*.csv',
                mime:'text/csv',
                beforeSend: function (environment) {


                    // The environment object can still be modified here.
                    // var {data, method, headers, xhr, responseType} = environment;

                },
                beforeAll: function () {
                },
                load: function () {
                },
                error: function () {
                    console.log('error', arguments);
                    let res = arguments[0].xhr.response;
                    let rv_dat = JSON.parse(res);
                    UIkit.notification({
                        message: rv_dat.message,
                        status: 'danger'
                    })
                    self.upload_error = 1
                    self.upload_success = 0
                },
                complete: function () {
                },

                loadStart: function (e) {
                    bar.removeAttribute('hidden');
                    bar.max = e.total;
                    bar.value = e.loaded;
                },

                progress: function (e) {
                    bar.max = e.total;
                    bar.value = e.loaded;
                },

                loadEnd: function (e) {
                    bar.max = e.total;
                    bar.value = e.loaded;
                },

                completeAll: function () {
                    console.log('completeAll', arguments);
                    let res = arguments[0].response;
                    let rv_dat = JSON.parse(res);
                    UIkit.notification({
                        message: 'Fichier envoyé avec success',
                        status: 'success'
                    })
                    self.success_data.mem_usage = rv_dat.mem_usage
                    self.success_data.nb_rows = rv_dat.nb_rows
                    self.success_data.file_name = rv_dat.file_name
                    self.success_data.path = rv_dat.path
                    setTimeout(function () {
                        bar.setAttribute('hidden', 'hidden');
                    }, 1000);

                    self.upload_success = 1
                    self.upload_error = 0

                }

            });

            // load modal




        },



        methods: {
            tes: function (){
                this.nb_num = 0;
            },

            check_number: function (){

                this.error_mngt.input_error = 0;

                if (this.p_number.length === 7) {

                    const regex = /^[347][0-9]{6}$/;

                    if (!regex.test(this.p_number)){
                        this.error_mngt.input_error = 1;
                    }
                }else {
                    this.error_mngt.input_error = 1;
                }

                this.fill_operator();

            },

            fill_operator:function (){
                this.operator = "";
                if(this.error_mngt.input_error === 0 && this.p_number !== ''){
                    let op = parseInt(this.p_number.charAt(0))
                    switch (op) {
                        case 3 :
                            this.operator = "HURI"
                            break;
                        case 4 :
                            this.operator = "TELMA"
                            break;
                        case 7 :
                            this.operator = "HURI"
                            break;
                    }
                }
            },

            back_dat: function (event){
                event.preventDefault();
                console.log(this.nb_num);
                console.log(this.data_push);

                let modal = UIkit.modal('#modal-center', {'bgClose':false}).show();

                setTimeout(function(modal) {
                    UIkit.modal('#modal-center', {'bgClose':false}).hide();
                }, (2 * 1000));

                this.upload_error = 0;
                this.error_mngt.input_error = 0;

                this.nb_num -= 1;
                this.p_number = this.data_push[this.nb_num - 1].p_number;
                this.operator = this.data_push[this.nb_num - 1].operator;
                this.link = this.data_push[this.nb_num - 1].success_data.link;
                this.success_data = this.data_push[this.nb_num - 1].success_data;
                if (this.link !== '') {
                    this.upload_success = 1;
                }
            },

            push_data: function (event){
                event.preventDefault();
                console.log(this.recap_status)
                // check if error in form

                if (this.p_number === '' || this.operator ==='' || this.success_data.file_name === '' || this.error_mngt.input_error !== 0 || this.upload_error !== 0) {

                    UIkit.notification({
                        message: "Certaines valeurs sont incorrects",
                        status: 'danger'
                    })

                }else {
                    // push data to array

                    // show spinner
                    let temp_data_object = {
                        'p_number': this.p_number,
                        'nb_num': this.nb_num,
                        'operator': this.operator,
                        'link': this.success_data.file_name,
                        'success_data': this.success_data
                    }
                    this.data_push[this.nb_num - 1] = temp_data_object;

                    this.p_number = this.data_push[this.nb_num].p_number;
                    this.operator = this.data_push[this.nb_num].operator;
                    this.link = this.data_push[this.nb_num].success_data.link;
                    this.success_data = this.data_push[this.nb_num].success_data;
                    if (this.link !== '') {
                        this.upload_success = 1;
                    }else {
                        this.upload_error = 1;
                    }

                    // check if next element exists

                    if (typeof this.data_push[this.nb_num] !== 'undefined' && this.data_push[this.nb_num] !== null) {
                        this.p_number = this.data_push[this.nb_num].p_number;
                        this.operator = this.data_push[this.nb_num].operator;
                        this.link = this.data_push[this.nb_num].success_data.link;
                        this.success_data = this.data_push[this.nb_num].success_data;
                        if (this.link !== '') {
                            this.upload_success = 1;
                        }else {
                            this.upload_error = 1;
                        }
                    }else {
                        if (this.recap_status === 0) {
                            this.p_number = '';
                            this.operator = '';
                            this.link = '';
                            this.success_data = {
                                'nb_rows': '',
                                'mem_usage': '',
                                'file_name': '',
                                'path': ''
                            };
                            this.upload_error = 0;
                            this.upload_success = 0;
                        }
                    }



                    this.nb_num += 1;

                    let modal = UIkit.modal('#modal-center', {'bgClose':false}).show();

                    setTimeout(function(modal) {
                        UIkit.modal('#modal-center', {'bgClose':false}).hide();
                    }, (2 * 1000));


                }


            },

            new_user: function (event){
                event.preventDefault()
                this.p_number = '';
                this.operator = '';
                this.link = '';
                this.success_data = {
                    'nb_rows': '',
                    'mem_usage': '',
                    'file_name': '',
                    'path': ''
                };
                this.upload_error = 0;
                this.upload_success = 0;
            },

            recap_data: function (event) {
                this.recap_status = 1;
                event.preventDefault();
                let modal = UIkit.modal('#modal-recap', {'bgClose':false, 'escClose':false}).show();
            },

            validate_data: function () {
                // let modal = UIkit.modal('#modal-center', {'bgClose':false}).show();
                if (this.data_push.length > 1){
                    console.log(this.data_push);

                    axios({
                        method: 'post',
                        url: '/wizard/validate',
                        responseType: 'json',
                        data: this.data_push
                    }).then(function (response){
                        console.log(response)
                        let modal = UIkit.modal('#modal-center', {'bgClose':false}).hide();
                        UIkit.notification({
                            message: "Données importes avec succes",
                            status: 'success'
                        })
                        window.location.replace("/");
                    });

                }else {
                    UIkit.notification({
                        message: "Vous devez traiter au moins deux numeros",
                        status: 'danger'
                    })
                }
            }
        },



    })

}