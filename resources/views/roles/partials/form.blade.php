<div class="border rounded card-body border-secondary mb-3">
    <div class="form-row">
        <div class="card-body">
            <div class="form-group">
                <label for="name">Nombre<span style="color: red">*</span></label>
                <input type="text" name="name" class="form-control" placeholder="Ingrese nombre del rol"
                    value="{{ old('name', $role->name ?? '') }}">
                @error('name')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <h1>LISTA DE PERMISOS</h1>
            <div class="form-row" id="FormRole">
                @foreach ($groupedPermissions as $moduleKey => $subModules)
                    @php
                        // Obtener el primer permiso de los submódulos (o el permiso principal si no hay submódulos)
                        $moduleData = isset($subModules['']) ? $subModules[''][0] : null;
                        $moduleTitle = $moduleData ? $moduleData->description : $moduleKey;
                        $modulePermission = $moduleData ? $moduleData : null; // Asegurarse de que sea un objeto

                        // Contar submódulos (excluyendo la clave "")
                        $subModuleKeys = array_keys($subModules->toArray());
                        $subModuleKeys = array_filter($subModuleKeys, fn($key) => $key !== '');
                    @endphp

                    <div class="card border-secondary mb-3">
                        <div class="card-header bg-info text-white">
                            <div class="form-check">
                                @if ($modulePermission)
                                    <input class="form-check-input module-checkbox" type="checkbox" name="permissions[]" value="{{ $modulePermission->id }}" 
                                        id="permission-{{ $modulePermission->id }}" data-module="{{ $moduleKey }}" {{-- <!-- Usamos $moduleKey en lugar de $module --> --}}
                                        @if(isset($role) && $role->hasPermissionTo($modulePermission->name)) checked @endif>
                                    <label class="form-check-label" for="permission-{{ $modulePermission->id }}">
                                        <strong>{{ $moduleTitle }}</strong>
                                    </label>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                {{-- Si hay submódulos, generamos las columnas correspondientes --}}
                                @if (count($subModuleKeys) > 0)
                                    @foreach ($subModuleKeys as $index => $subModuleKey)
                                        @php
                                            $subModule = $subModuleKey; // Nombre del submódulo
                                            $subModulePermissions = $subModules[$subModuleKey];
                                    
                                            // Buscar el permiso de submódulo
                                            $subModulePermission = $subModulePermissions->first(fn($p) => strpos($p->name, '.submodule') !== false);
                                            
                                            // Obtener el título y el ID del submódulo si existe
                                            $subModuleTitle = $subModulePermission ? $subModulePermission->description : '';
                                            $subModuleId = $subModulePermission ? $subModulePermission->id : null;
                                        @endphp

                                        <div class="col-lg-4 col-md-6 col-12">
                                            <div class="card border-light shadow-sm mb-3">
                                                @if ($subModuleId)
                                                    <div class="card-header bg-secondary text-white">
                                                        <label class="w-100 mb-0 d-flex align-items-center">
                                                            <input type="checkbox" name="permissions[]" value="{{ $subModuleId }}" class="mr-1 submodule-checkbox" data-submodule="{{ $subModule }}" data-module="{{ $moduleKey }}"
                                                                @if(isset($role) && $role->hasPermissionTo($subModulePermission->name)) checked @endif>
                                                            <strong>{{ $subModuleTitle }}</strong>
                                                        </label>
                                                    </div>
                                                @endif
                                                <div class="card-body">
                                                    @foreach ($subModulePermissions as $permission)
                                                        {{-- Filtramos los permisos que no contienen ".submodule" --}}
                                                        @if (strpos($permission->name, '.submodule') === false)
                                                            <div class="mb-2">
                                                                <label class="d-block border rounded p-2">
                                                                    <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" class="mr-1 submodule-permission" data-submodule="{{ $subModuleKey }}"
                                                                        @if(isset($role) && $role->hasPermissionTo($permission->name)) checked @endif>
                                                                    {{ $permission->description }}
                                                                </label>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else {{-- Si no hay submódulos, mostramos el permiso en una columna completa --}}
                                    <div class="col-lg-4 col-md-6 col-12">
                                        @foreach ($subModules[''] as $permission)
                                            <div class="mb-2">
                                                <label class="d-block border rounded p-2">
                                                    <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" class="mr-1 submodule-permission" data-submodule="{{ '' }}"
                                                        @if(isset($role) && $role->hasPermissionTo($permission->name)) checked @endif>
                                                    {{ $permission->description }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
