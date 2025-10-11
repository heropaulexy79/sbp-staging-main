// import { Question } from "@/Pages/Organisation/Course/Lesson/Partials/use-quiz-manager";

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at: string;
    account_type: "ORG" | "TEACHER";
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User & {
            organisation_id: Organisation["id"] | null;
            role: string | null;
        };
    };
    query: { [key: string]: string };
    global: { [key: string]: string | boolean };
    flash: {
        [key: string]: any;
        message?: {
            status: "success" | "error";
            message: string;
            action?: {
                "cta:link": string;
                "cta:text": string;
            };

            [k: string]: any;
        };
        "global:message"?: {
            status: "success" | "error";
            message: string;
            action?: {
                "cta:link": string;
                "cta:text": string;
            };
        };
    };
};

export interface Organisation {
    id: number;
    name: string;
}

export interface OrganisationUser {
    id: number;
    user_id: User["id"];
    organisation_id: Organisation["id"];
    user: Pick<User, "id" | "name" | "email">;
    role: "ADMIN" | "STUDENT";
}

export interface OrganisationInvite {
    id: number;
    email: string;
    role: string;
    organisation_id: Organisation["id"];
}

export interface Course {
    id: number;
    title: string;
    slug: string;
    description: string;
    teacher_id: User["id"];
    is_published: boolean;
    banner_image: string | null;

    created_at: Date;
    deleted_at: Date | null;
    updated_at: Date | null;
}

export interface Lesson {
    id: number;
    title: string;
    slug: string;
    content: string;
    content_json: Question[];
    course_id: Course["id"];
    type: "DEFAULT" | "QUIZ" | (string & {});

    is_published: boolean;

    created_at: Date;
    deleted_at: Date | null;
}

export type Question = SingleChoice | MultipleChoice;

type MultipleChoice = {
    id: string;
    text: string;
    type: "multiple_choice";
    options: Array<QuestionOption>;
    correct_option: string[];
};
type SingleChoice = {
    id: string;
    text: string;
    type: "single_choice";
    options: Array<QuestionOption>;
    correct_option: string;
};

type QuestionOption = {
    id: string;
    text: string;
};

export type Paginated<T> = {
    current_page: number;
    data: T[];
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    links: {
        url?: string;
        label: string;
        active: boolean;
    }[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number;
    total: number;
};

export type PaymentMethod = {
    id: number;
    country: string;

    bank?: string | null;

    first_six: string;
    last_four: string;
    card_type: string;
    exp_month: string;
    exp_year: string;

    reusable: boolean;

    account_name: string;
    email_address: string;

    organisation_id: number;

    created_at?: Date | null;
    updated_at?: Date | null;
};

export type BillingHistory = {
    id: number;
    transaction_ref: string;

    currency: string;
    amount: number;
    description: string;
    provider: "PAYSTACK";

    organisation_id: number;

    created_at?: Date | null;
    updated_at?: Date | null;
};

export type Subscription = {
    id: number;
    plan: "starter" | "enterprise";
    status: "active" | "inactive";
    description: string;
    organisation_id: Organisation["id"];

    amount: number;
    currency: "NGN";

    billed_at?: Date | null;
    next_billing_date?: Date | null;

    created_at?: Date | null;
    updated_at?: Date | null;
};

export type SubscriptionPlan = {
    id: string;
    name: string;
    price: number;
    currency: string;
    description: string;
    features: string[];
};

export interface Group {
    id: number;
    name: string;
    organisation_id: Organisation["id"];
    users: User[];
    users_count: number;
    created_at: Date;
    updated_at: Date;
    deleted_at: Date | null;
}
