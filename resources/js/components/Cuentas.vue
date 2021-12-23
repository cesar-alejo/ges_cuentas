<template>
    <div class="row">
        <div class="col text-center">
            <h1><span class="badge bg-secondary">Gestión Cuentas De Ahorro</span></h1>
        </div>

        <div class="col-12 mb-2">
            <button type="button" class="btn btn-success" @click="openModal(1)" title="Asignar Cuenta a Usuario.">
                <i class="fas fa-plus-circle"></i>
            </button>
            <!-- <router-link :to='{name:"crearcuenta"}' class="btn btn-success"></router-link> -->
        </div>
        
        <div class="col-12" v-if="mostrar">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead class="bg-primary text-white">
                        <th class="p-2">#</th>
                        <th class="p-2">Cuenta</th>
                        <th class="p-2">Titular</th>
                        <th class="p-2">Saldo Actual</th>
                        <th class="p-2">Acciones</th>
                    </thead>
                    <tbody>
                        <tr v-for="(cuenta, index) in cuentas.data" :key="cuenta.cuenta_id">
                            <td>{{ index+1 }}</td>
                            <td>{{ cuenta.cuenta_id ? cuenta.cuenta_id : 'Sin Cuenta Asignada'}}</td>
                            <td>{{ cuenta.titular }}</td>
                            <td>{{ cuenta.valor ? cuenta.valor : 0 }}</td>
                            <td>
                                <a @click="openModal(2, cuenta.cuenta_id)" title="Consignar dinero" class="btn btn-success">
                                    <i class="fas fa-money-bill-alt"></i>
                                </a>
                                <a @click="openModal(3, cuenta.cuenta_id)" title="Retirar dinero" class="btn btn-primary">
                                    <i class="far fa-money-bill-alt"></i>
                                </a>
                                <a @click="openModal(4, cuenta.cuenta_id)" title="Consultar saldo" class="btn btn-secondary">
                                    <i class="fas fa-money-check-alt"></i>
                                </a>
                            </td>                                                
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="row">
                <div class="col-3 text-primary text-end">
                    {{cuentas.from}} - {{cuentas.to}} de {{cuentas.total}} en total
                </div>
                <div class="col-2">
                    <select class="form-select" v-model="pagination.per_page" @change="mostrarCuentas()">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="15">15</option>
                    </select>
                </div>
                <div class="col-4">
                    <nav aria-label="Page navigation example">
                        <ul class="pagination justify-content-center">
                            <li class="page-item" :class="{disabled:pagination.page==1}">
                                <a href="#" class="page-link" @click="pagination.page=1; mostrarCuentas()">&laquo;</a>
                            </li>
                            <li class="page-item" :class="{disabled:pagination.page==1}">
                                <a href="#" class="page-link" @click="pagination.page--; mostrarCuentas()">&lt;</a>
                            </li>
                            <li class="page-item" v-for="n in paginas" :key="n" :class="{active:pagination.page==n}" >
                                <a href="#" class="page-link" @click="pagination.page=n; mostrarCuentas()">{{n}}</a>
                            </li>
                            <li class="page-item" :class="{disabled:pagination.page==cuentas.last_page}">
                                <a href="#" class="page-link" @click="pagination.page++; mostrarCuentas()">&gt;</a>
                            </li>
                            <li class="page-item" :class="{disabled:pagination.page==cuentas.last_page}">
                                <a href="#" class="page-link" @click="pagination.page=cuentas.last_page; mostrarCuentas()">&raquo;</a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <div class="col-3"></div>
            </div>
        </div>
        <div class="col-12" v-else>
            Cargando... Espere por favor
        </div>

        <!-- Modal Asignar Cuenta -->
        <div class="modal" :class="{'show-modal':modal}" tabindex="-1" aria-hidden="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ modalTitle }}</h5>
                        <button type="button" @click="closeModal()" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div v-if="modalType==1"><form @submit.prevent="asignarCuenta">
                        <div class="modal-body">

                            <div class="form-group mb-3">
                                <label for="">Titular</label>
                                <select class="form-select" v-model="asignar.user_id">
                                    <option value="">-- Seleccione el titular --</option>
                                    <option v-for="user in users" :key="user.value" v-bind:value="user.id">
                                        {{ user.name }}
                                    </option>
                                </select>
                                <span class="text-danger" v-if="errors.user_id">{{ errors.user_id[0] }}</span>
                            </div>

                            <div class="form-group">
                                <label for="">Valor Inicial</label>
                                <input type="number" class="form-control" v-model="asignar.valor">
                                <span class="text-danger" v-if="errors.valor">{{ errors.valor[0] }}</span>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" @click="closeModal()" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Asignar</button>
                        </div>
                    </form></div>

                    <div v-if="modalType==2"><form @submit.prevent="consigCuenta">
                        <div class="modal-body">

                            <div class="form-group mb-3">
                                <label for="">Cuenta a consignar</label>
                                <input type="number" class="form-control" v-model="cuenta.cuenta_id" disabled>
                                <span class="text-danger" v-if="errors.cuenta_id">{{ errors.cuenta_id[0] }}</span>
                            </div>

                            <div class="form-group">
                                <label for="">Valor a Consignar</label>
                                <input type="number" class="form-control" v-model="cuenta.valor">
                                <span class="text-danger" v-if="errors.valor">{{ errors.valor[0] }}</span>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" @click="closeModal()" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Consignar</button>
                        </div>
                    </form></div>

                    <div v-if="modalType==3"><form @submit.prevent="retiroCuenta">
                        <div class="modal-body">

                            <div class="form-group mb-3">
                                <label for="">Cuenta a debitar</label>
                                <input type="number" class="form-control" v-model="cuenta.cuenta_id" disabled>
                                <span class="text-danger" v-if="errors.cuenta_id">{{ errors.cuenta_id[0] }}</span>
                            </div>

                            <div class="form-group">
                                <label for="">Valor a Debitar</label>
                                <input type="number" class="form-control" v-model="cuenta.valor">
                                <span class="text-danger" v-if="errors.valor">{{ errors.valor[0] }}</span>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" @click="closeModal()" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Retirar</button>
                        </div>
                    </form></div>

                    <div v-if="modalType==4">
                        <div class="modal-body">
                            <div class="col text-center">
                                <h2><span class="badge bg-primary w-75">{{ saldoActual }}</span></h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>
<script lang="ts">
export default {
    name:"list-cuentas",
    data() {
        return{
            modal:0,modalId:0,modalType:0,modalTitle:'',
            cuentas:[],
            pagination:{
                page:1,
                per_page:5,
            },
            paginas:[],
            mostrar:false,
            selUser: '',
            users: [],
            asignar:{
                user_id:'',
                valor:''
            },
            cuenta:{
                cuenta_id:'',
                valor:''
            },
            errors:{
                user_id:"",
                valor:""
            },
            saldoActual:0,
        }
    },
    mounted(){
        this.mostrarCuentas()
    },
    methods:{
        async mostrarCuentas(){
            await axios.get('/api/cuentas', {params:this.pagination})
                .then((result) => {
                    this.cuentas = result.data;
                    this.listPage();
                    this.mostrar=true;
                }).catch((err) => {
                    this.cuentas = []
                    console.log(err)
                });

            await axios.get('/api/cusers')
                .then((result) => { 
                    this.users = result.data;
                }).catch((err) => {
                    this.users = []
                    console.log(err)
                });
        },
        listPage(){
            const n=2; this.paginas=[];
            let ini = this.pagination.page - 2;
            if (ini < 1) ini=1
            let fin = this.pagination.page + 2;
            if (fin > this.cuentas.last_page) fin=this.cuentas.last_page;
            for (let i = ini; i <= fin; i++) {
                this.paginas.push(i);
            }
        },
        async asignarCuenta(){
            await axios.post('/api/cuentas', this.asignar)
                .then((result) => {
                    this.mostrarCuentas()
                    this.closeModal()
                    this.errors = {}
                }).catch((err) => {
                    if (err.response.data){
                        this.errors = err.response.data.errors
                    } else { console.log(err) }
                });
        },
        async consigCuenta(){
            axios.put(`/api/cuentas/${this.cuenta.cuenta_id}`, this.cuenta)
                .then((result) => {
                    this.mostrarCuentas()
                    this.closeModal()
                    this.errors = {}; this.cuenta = {}
                }).catch((err) => {
                    if (err.response.data){
                        this.errors = err.response.data.errors
                    } else { console.log(err) }
                });
        },
        async retiroCuenta(){
            axios.delete(`/api/cuentas/${this.cuenta.cuenta_id}`, {params: this.cuenta})
                .then((result) => {
                    this.mostrarCuentas()
                    this.closeModal()
                    this.errors = {}; this.cuenta = {}
                }).catch((err) => {
                    if (err.response.data){
                        this.errors = err.response.data.errors
                    } else { console.log(err) }
                });
        },
        async consultSaldo(){
            await axios.get('/api/cuentas/' + this.modalId)
                .then((result) => {
                    this.saldoActual = result.data.valor;
                }).catch((err) => {
                    console.log(err)
                });
        },
        openModal(action, id=0){
            this.modal = 1; this.modalType = action; this.modalId = id;
            this.cuenta.cuenta_id = id;
            if (action === 1) this.modalTitle = 'Asignar Cuenta'
            if (action === 2) this.modalTitle = 'Consignar A Cuenta: ' + id
            if (action === 3) this.modalTitle = 'Retirar De Cuenta: ' + id
            if (action === 4) {
                this.modalTitle = 'Saldo Actual De Cuenta: ' + id
                this.saldoActual = 0
                this.consultSaldo()
            }
        },
        closeModal(){
            this.modal = this.modalId = 0;
            this.modalTitle = '';
            this.errors = {}; this.cuenta = {}
        }
    }
}
</script>
<style>
.show-modal { display: list-item; opacity: 1; background: rgba(105, 117, 122, 0.849); }
</style>