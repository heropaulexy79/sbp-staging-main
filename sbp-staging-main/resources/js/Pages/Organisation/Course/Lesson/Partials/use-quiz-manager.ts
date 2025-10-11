import { ref } from "vue";
import { generateId } from "./utils";
import { Question } from "@/types";

export function useQuizManager(initialQuestions?: Question[]) {
    const questions = ref<Array<Question>>(initialQuestions ?? []);

    const addQuestion = () => {
        questions.value.push({
            id: generateId(),
            text: "",
            type: "single_choice",
            options: [
                { id: generateId(), text: "" },
                { id: generateId(), text: "" },
            ],
        } as Question);
    };

    const deleteQuestion = (index: number) => {
        questions.value.splice(index, 1);
    };

    const addOption = (questionIndex: number) => {
        questions.value[questionIndex].options.push({
            id: generateId(),
            text: "",
        });
    };

    const deleteOption = (questionIndex: number, optionIndex: number) => {
        questions.value[questionIndex].options.splice(optionIndex, 1);
    };

    return {
        questions,
        addQuestion,
        deleteQuestion,
        addOption,
        deleteOption,
    };
}
