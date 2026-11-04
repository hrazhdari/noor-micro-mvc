<?php
declare(strict_types=1);
namespace App\Controllers;
use Noor\Core\Controller;
use Noor\Core\Validation;
use Noor\Core\View;
use Noor\Http\Request;
use Noor\Http\Response;
use Noor\Security\Csrf;
final class HomeController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->view('home', ['title' => 'Noor Micro MVC', 'baseUrl' => $request->url()]);
    }
    public function greet(Request $request, string $name): Response
    {
        return Response::json(['message' => 'Hello ' . $name]);
    }
    public function form(Request $request): Response
    {
        return Response::html('<!doctype html><html lang="fa" dir="rtl"><meta charset="UTF-8"><title>Noor Form</title>'
            . '<h1>نمونه فرم امن Noor</h1><form method="post" action="' . View::escape($request->url('/form')) . '">'
            . Csrf::field() . '<label>نام: <input name="name" maxlength="100" required></label>'
            . '<button type="submit">ارسال</button></form></html>');
    }
    public function submit(Request $request): Response
    {
        $errors = Validation::check($request->all(), ['name' => 'required|string|min:2|max:100']);
        if ($errors !== []) { return Response::json(['errors' => $errors], 422); }
        return Response::html('<!doctype html><html lang="fa" dir="rtl"><meta charset="UTF-8"><p>نام دریافت شد: '
            . View::escape($request->input('name')) . '</p></html>');
    }
}
