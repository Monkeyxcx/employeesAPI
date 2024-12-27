<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employees;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule; // Para reglas personalizadas, como ignorar el email actual
use Illuminate\Database\Eloquent\ModelNotFoundException; // Para manejar excepciones de modelo no encontrado

class employeesController extends Controller
{
    // index():
    // Obtiene todos los registros de empleados utilizando Employees::all().
    // Si no hay empleados, retorna un error 404 con un mensaje JSON indicando que no se encontraron empleados.
    // Si hay empleados, los retorna en formato JSON con un código de estado 200.

    function index()
    {
        $enployments = Employees::all();

        if ($enployments->isEmpty()) {
            $error = [
                'error' => 'Empleados no encontradas',
                'code' => 200,
            ];
            return response()->json($error, 404);
        }

        return response()->json($enployments, 200);
    }

    // store(Request $request):
    // Crea un nuevo empleado.
    // Valida los datos de la solicitud ($request) utilizando la clase Validator. Se definen reglas para los campos 'name', 'email', 'phone' y 'position', incluyendo mensajes de error personalizados.
    // Si la validación falla, retorna un error 422 con los errores de validación en formato JSON.
    // Si la validación es exitosa, crea un nuevo registro de empleado utilizando Employees::create().
    // Retorna una respuesta JSON con un código de estado 201 indicando que el registro fue creado exitosamente, incluyendo los datos del nuevo empleado.
    // Maneja excepciones y retorna un error 500 en caso de error al crear el registro

    public function store(Request $request)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:employees',
                'phone' => 'required|numeric|digits_between:8,15',
                'position' => 'required|string|max:255',
            ], [
                // Mensajes personalizados de error
                'name.required' => 'El nombre es obligatorio',
                'name.max' => 'El nombre no puede tener más de 255 caracteres',
                'email.required' => 'El correo electrónico es obligatorio',
                'email.email' => 'Por favor ingrese un correo electrónico válido',
                'email.unique' => 'Este correo electrónico ya está registrado',
                'phone.required' => 'El teléfono es obligatorio',
                'phone.numeric' => 'El teléfono debe contener solo números',
                'phone.digits_between' => 'El teléfono debe tener entre 8 y 15 dígitos',
                'position.required' => 'El cargo es obligatorio',
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Create new record
            $employees = Employees::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'position' => $request->position,
            ]);

            // Return success response
            return response()->json([
                'status' => 'success',
                'message' => 'Registro creado exitosamente',
                'data' => $employees
            ], 201);
        } catch (\Exception $e) {
            // Handle any other errors
            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear el registro',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // show($id):
    // Obtiene un empleado por su ID.
    // Busca el empleado utilizando Employees::findOrFail($id), que lanza una excepción si no se encuentra el empleado.
    // Retorna una respuesta JSON con un código de estado 200 y los datos del empleado si se encuentra.
    // Maneja excepciones ModelNotFoundException (empleado no encontrado) y retorna un error 404.
    // Maneja otras excepciones y retorna un error 500 en caso de error al obtener el empleado.


    public function show($id)
    {
        try {
            $employee = Employees::findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $employee
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Empleado no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener el empleado',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // delete($id):
    // Elimina un empleado por su ID.
    // Busca el empleado utilizando Employees::findOrFail($id).
    // Elimina el empleado utilizando $employee->delete().
    // Retorna una respuesta JSON con un código de estado 200 indicando que el empleado fue eliminado exitosamente.
    // Maneja excepciones ModelNotFoundException y retorna un error 404.
    // Maneja otras excepciones y retorna un error 500 en caso de error al eliminar el empleado.

    public function delete($id)
    {
        try {
            $employee = Employees::findOrFail($id);

            $employee->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Empleado eliminado exitosamente'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Empleado no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar el empleado',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // update(Request $request, $id):
    // Actualiza un empleado existente.
    // Busca el empleado utilizando Employees::findOrFail($id).
    // Valida los datos de la solicitud similar a la función store(), pero utiliza Rule::unique('employees')->ignore($employee->id) para permitir que el email actual del empleado no se considere como un error de validación de unicidad.
    // Si la validación falla, retorna un error 422.
    // Si la validación es exitosa, actualiza el empleado utilizando $employee->update().
    // Retorna una respuesta JSON con un código de estado 200 indicando que el registro fue actualizado exitosamente, incluyendo los datos del empleado actualizado.
    // Maneja excepciones ModelNotFoundException y retorna un error 404.
    // Maneja otras excepciones y retorna un error 500 en caso de error al actualizar el empleado.

    public function update(Request $request, $id)
    {
        try {
            // Buscar el empleado existente
            $employee = Employees::findOrFail($id);

            // Validar los datos del request
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => [
                    'required',
                    'string',
                    'email',
                    'max:255',
                    Rule::unique('employees')->ignore($employee->id), // Permitir el email actual
                ],
                'phone' => 'required|numeric|digits_between:8,15',
                'position' => 'required|string|max:255',
            ], [
                // Mensajes personalizados de error
                'name.required' => 'El nombre es obligatorio',
                'name.max' => 'El nombre no puede tener más de 255 caracteres',
                'email.required' => 'El correo electrónico es obligatorio',
                'email.email' => 'Por favor ingrese un correo electrónico válido',
                'email.unique' => 'Este correo electrónico ya está registrado',
                'phone.required' => 'El teléfono es obligatorio',
                'phone.numeric' => 'El teléfono debe contener solo números',
                'phone.digits_between' => 'El teléfono debe tener entre 8 y 15 dígitos',
                'position.required' => 'El cargo es obligatorio',
            ]);

            // Verificar si la validación falla
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Actualizar el registro del empleado
            $employee->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'position' => $request->position,
            ]);

            // Retornar respuesta de éxito
            return response()->json([
                'status' => 'success',
                'message' => 'Registro actualizado exitosamente',
                'data' => $employee
            ], 200);
        } catch (ModelNotFoundException $e) {
            // Si el empleado no existe
            return response()->json([
                'status' => 'error',
                'message' => 'Empleado no encontrado',
            ], 404);
        } catch (\Exception $e) {
            // Manejar cualquier otro error
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar el registro',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
