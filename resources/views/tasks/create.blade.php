@extends('layouts.master')

@section('pageTitle', $pageTitle)

@section('main')
    <div class="form-container">
        <h1 class="form-title">{{ $pageTitle }}</h1>
        <form class="form" action="{{ route('tasks.store') }}" method="POST">
            @csrf
            <div class="form-item">
                <label>Name:</label>
                <input class="form-input" type="text" value="{{ old('name') }}" name="name">
                @error('name')
                    <div class="alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-item">
                <label>Detail:</label>
                <textarea class="form-text-area" name="detail">{{ old('detail') }}</textarea>
            </div>

            <div class="form-item">
                <label>Due Date:</label>
                <input class="form-input" type="date" value="{{ old('due_date') }}" name="due_date">
                @error('due_date')
                    <div class="alert-danger">{{ $message }}</div>
                @enderror
            </div>

            @php
                $status = old('status', $status);
            @endphp
            <div class="form-item">
                <label>Progress:</label>
                <select class="form-input" name="status">
                    <option value="not_started" @if($status == 'not_started') selected @endif>Not Started</option>
                    <option value="in_progress" @if($status == 'in_progress') selected @endif>In Progress</option>
                    <option value="in_review" @if($status == 'in_review') selected @endif>Waiting/In Review</option>
                    <option value="completed" @if($status == 'completed') selected @endif>Completed</option>
                </select>
                @error('status')
                    <div class="alert-danger">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="form-button">Submit</button>
        </form>
    </div>
@endsection
